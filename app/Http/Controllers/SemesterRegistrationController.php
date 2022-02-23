<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Course;
use App\Models\Student;
use App\Models\Subscribe;
use App\Service\Payment\Checkout;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use function GuzzleHttp\json_decode;

class SemesterRegistrationController extends Controller
{

    public function index()
    {
        if(request()->query('cko-session-id')){
            $client = new Client(['base_uri' => 'https://api.checkout.com']);

            try {
                $response = $client->request('GET', '/payments/' . request()->query('cko-session-id'),
                    [
                        'headers' => [
                            'Authorization' => "sk_f9b4d5dd-d1d0-4943-bdbf-e5cd88f37403"
                        ]
                    ]);

                $data = json_decode($response->getBody()->getContents());

                if ($response->getStatusCode() != 404){

                    $subscribe = Subscribe::query()
                        ->where('payment_id', '=', $data->id)
                        ->first();

                    $result = $subscribe->update([
                        'payment_status' => $data->status,
                        'response_code'  => $data->actions[0]->response_code ?? '-',
                    ]);

                    if ($data->approved){
                        session()->flash('success', __('resubscribe.The registration process has been completed successfully'));
                    }else{
                        session()->flash('error', __('resubscribe.Payment failed'));
                    }

                }else{
                    session()->flash('error', __('resubscribe.Payment failed'));
                }

                return redirect()->route('semester.registration.index');
            }catch (\GuzzleHttp\Exception\ClientException $e) {
//                $response = $e->getResponse();
                session()->flash('error', __('resubscribe.Payment failed'));
                return redirect()->route('semester.registration.index');
            }
        }

        $countries = Country::query()->where('lang', '=', App::getLocale())->get();
        $course = Course::query()->where('code', 'regular')->first();

        return view('old_students', ['countries' => $countries, 'course' => $course]);
    }

    public function store(Request $request)
    {

    }

    public function getStudentInfo()
    {
        $student = Student::query()
            ->where('serial_number', '=', \request()->std_number)
            ->where('section', '=', \request()->std_section)
            ->first();


        if ($student){
            return response()->json(['name' => $student->name], 200, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json(['name' => $student->name], 500);

    }
}

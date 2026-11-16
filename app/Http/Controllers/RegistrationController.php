<?php

namespace App\Http\Controllers;

use App\Services\ParticipantApi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegistrationController extends Controller
{
    public function create()
    {
        return view('registrations.create');
    }

    public function store(Request $request, ParticipantApi $api)
    {
        // Spam trap: real visitors never see or fill this field
        if ($request->filled('website')) {
            return redirect()->route('registrations.create')->with('registered', 'Your registration was submitted.');
        }

        $yesNo = Rule::in(['true', 'false']);

        $data = $request->validate([
            'firstName'       => ['required', 'string', 'max:100'],
            'midName'         => ['nullable', 'string', 'max:100'],
            'lastName'        => ['required', 'string', 'max:100'],
            'extName'         => ['nullable', 'string', 'max:20'],
            'email'           => ['required', 'email', 'max:190'],
            'contactNo'       => ['required', 'string', 'regex:/^[0-9+\-\s()]{7,30}$/'],
            'sex'             => ['required', Rule::in(['Male', 'Female'])],
            'ageBracket'      => ['required', Rule::in(config('event.age_brackets'))],
            'isIndigenous'    => ['required', $yesNo],
            'indigenousGroup' => ['required_if:isIndigenous,true', 'nullable', 'string', 'max:150'],
            'withDisability'  => ['required', $yesNo],
            'disability'      => ['required_if:withDisability,true', 'nullable', 'string', 'max:150'],
            'isPhilriceEmp'   => ['required', $yesNo],
            'philriceName'    => ['required_if:isPhilriceEmp,true', 'nullable', 'string', 'max:150'],
            'philriceStation' => ['required_if:isPhilriceEmp,true', 'nullable', 'string', 'max:150'],
            'philriceUnit'    => ['required_if:isPhilriceEmp,true', 'nullable', 'string', 'max:150'],
            'affiliationName' => ['required_if:isPhilriceEmp,false', 'nullable', 'string', 'max:190'],
        ], [
            'contactNo.regex' => 'Enter a valid mobile number, for example 09171234567.',
        ], [
            'isIndigenous'   => 'Indigenous group answer',
            'withDisability' => 'disability answer',
            'isPhilriceEmp'  => 'PhilRice employee answer',
            'contactNo'      => 'mobile number',
        ]);

        $v = fn (string $key) => trim((string) ($data[$key] ?? ''));

        $indigenous = $v('isIndigenous') === 'true';
        $disabled   = $v('withDisability') === 'true';
        $employee   = $v('isPhilriceEmp') === 'true';

        // Details that don't apply are sent blank, so a changed answer never leaves stale values behind
        $payload = [
            'firstName'       => $v('firstName'),
            'midName'         => $v('midName'),
            'lastName'        => $v('lastName'),
            'extName'         => $v('extName'),
            'email'           => $v('email'),
            'sex'             => $v('sex'),
            'ageBracket'      => $v('ageBracket'),
            'isIndigenous'    => $v('isIndigenous'),
            'indigenousGroup' => $indigenous ? $v('indigenousGroup') : '',
            'withDisability'  => $v('withDisability'),
            'disability'      => $disabled ? $v('disability') : '',
            'contactNo'       => $v('contactNo'),
            'isPhilriceEmp'   => $v('isPhilriceEmp'),
            'philriceName'    => $employee ? $v('philriceName') : 'N/A',
            'philriceStation' => $employee ? $v('philriceStation') : 'N/A',
            'philriceUnit'    => $employee ? $v('philriceUnit') : 'N/A',
            'affiliationName' => $employee ? '' : $v('affiliationName'),
        ];

        $result = $api->register($payload);

        if (! $result['ok']) {
            // A bad API key or a server fault is for you to fix, not for the public to read
            if (in_array($result['status'], [401, 403]) || $result['status'] >= 500) {
                report(new \RuntimeException("Registration API error {$result['status']}: {$result['message']}"));

                return back()->withInput()->with('error', 'Registration is temporarily unavailable. Please try again in a few minutes.');
            }

            $fieldErrors = is_array($result['errors'] ?? null) ? $result['errors'] : [];

            // Spell out what the API objected to, for example "Validation failed. The email has already been taken."
            $detail = collect($fieldErrors)
                ->map(fn ($m) => is_array($m) ? implode(' ', $m) : (string) $m)
                ->filter()
                ->implode(' ');

            return back()
                ->withInput()
                ->withErrors(array_intersect_key($fieldErrors, $payload))   // highlights the matching fields
                ->with('error', trim($result['message'] . ' ' . $detail));
        }

        return redirect()->route('registrations.create')->with('registered', $result['message']);
    }
}
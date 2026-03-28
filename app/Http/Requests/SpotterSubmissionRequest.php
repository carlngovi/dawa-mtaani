<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SpotterSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|string|max:36',
            'pharmacy' => 'required|string|max:255',
            'town' => 'required|string|max:255',
            'ward' => 'required|string|max:255',
            'county' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'gpsAccuracy' => 'nullable|numeric|min:0',
            'openTime' => 'required|string|max:8',
            'closeTime' => 'required|string|max:8',
            'daysPerWeek' => 'required|string|max:1',
            'photoData' => 'nullable|string',
            'photoName' => 'nullable|string|max:255',
            'ownerName' => 'required|string|max:255',
            'ownerPhone' => 'required|string|max:30',
            'pharmacyPhone' => 'nullable|string|max:30',
            'ownerEmail' => 'nullable|email|max:255',
            'ownerPresent' => 'required|in:Yes,No',
            'followUp' => 'required|in:Yes,No',
            'callbackTime' => 'nullable|string|max:100',
            'footTraffic' => 'required|in:high,medium,low',
            'stockLevel' => 'required|in:well_stocked,moderate,sparse,not_observed',
            'potential' => 'required|in:high,medium,low',
            'notes' => 'nullable|string',
            'nextStep' => 'required|in:sales_rep,spotter_followup,owner_absent,no_action',
            'followUpDate' => 'required_unless:nextStep,no_action|date',
            'repNotes' => 'nullable|string',
            'brochure' => 'required|in:Yes,No',
            'status' => 'nullable|in:submitted,held,draft',
            'date' => 'required|date',
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Enums\UniversityEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     * 
     */
    public function rules(): array
    {
        return [
            "full_name" => ["required", "string", "max:255"],
            "email" => ["required", "string", "email", "max:255", "unique:users"],
            "qualification" => ["required", "string", "max:255"],
            "documents"   => ["required", "array"],
            "documents.*" => ["file", "mimes:pdf,jpg,jpeg,png", "max:5048"],
            "age" => ["required", "integer", "min:18"],
            "university" => ["required", "string", "max:255", Rule::enum(UniversityEnum::class)],
            "course" => ["required", "string", "max:255"],
            "terms" => ["required", "accepted"],
        ];
    }

    public function messages(): array
    {
        return [
            "full_name.required" => "Full name is required.",
            "email.required" => "Email is required.",
            "email.email" => "Email must be a valid email address.",
            "email.unique" => "Email has already been taken.",
            "qualification.required" => "Qualification is required.",
            "age.required" => "Age is required.",
            "age.min" => "Age must be at least 18.",
            "university.required" => "University is required.",
            "university.string" => "University must be a string.",
            "university.max" => "University must not exceed 255 characters.",
            "university.enum" => "Selected university is invalid.",
            "course.required" => "Course is required.",
            "course.string" => "Course must be a string.",
            "course.max" => "Course must not exceed 255 characters.",
            "terms.accepted" => "You must accept the terms and conditions.",
            "documents.array" => "Documents must be an array of files.",
            "documents.*.file" => "Each document must be a file.",
            "documents.*.mimes" => "Documents must be of type: pdf, jpg, jpeg, png.",
            "documents.*.max" => "Each document must not exceed 5MB in size.",
        ];
    }
}

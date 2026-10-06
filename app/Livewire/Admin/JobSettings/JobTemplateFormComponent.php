<?php

namespace App\Livewire\Admin\JobSettings;

use Livewire\Component;
use App\Models\JobTemplate;
use App\Models\JobCategory;

class JobTemplateFormComponent extends Component
{
    public $templateId;
    public $title, $category, $overview, $description, $job_type, $default_salary_min, $default_salary_max, $distance;
    public $job_city = '';
    
    // New Detailed Fields
    public $minimum_education;
    public $english_level;
    public $min_experience_years;
    public $max_experience_years;
    public $gender_preference;
    public $interview_information = [];
    public $additional_perks = [];
    public $joining_fee_required = false;
    public $salary_type;

    public $default_screening_questions = [];
    public $step = 1;
    public $isEditMode = false;

    public function mount($id = null)
    {
        if ($id) {
            $this->isEditMode = true;
            $this->templateId = $id;
            $template = JobTemplate::findOrFail($id);

            $this->title = $template->title;
            $this->category = $template->category;
            $this->overview = $template->overview;
            $this->description = $template->description;
            $this->job_type = $template->job_type;
            $this->default_salary_min = $template->default_salary_min;
            $this->default_salary_max = $template->default_salary_max;
            $this->distance = $template->distance;
            $this->job_city = $template->job_city ?? '';
            
            // Map New Detailed Fields
            $this->minimum_education = $template->minimum_education;
            $this->english_level = $template->english_level;
            $this->min_experience_years = $template->min_experience_years;
            $this->max_experience_years = $template->max_experience_years;
            $this->gender_preference = $template->gender_preference;
            $this->interview_information = is_array($template->interview_information) ? $template->interview_information : (json_decode($template->interview_information, true) ?? []);
            $this->additional_perks = is_array($template->additional_perks) ? $template->additional_perks : (json_decode($template->additional_perks, true) ?? []);
            $this->joining_fee_required = $template->joining_fee_required;
            $this->salary_type = $template->salary_type;

            $questions = is_array($template->default_screening_questions) 
                ? $template->default_screening_questions 
                : json_decode($template->default_screening_questions, true) ?? [];
                
            foreach ($questions as &$q) {
                if (!isset($q['id'])) {
                    $q['id'] = uniqid('q_');
                }
            }
            $this->default_screening_questions = $questions;
        }
    }

    public function nextStep()
    {
        if ($this->step === 1) {
            $this->validate([
                'title' => 'required|string|max:255',
                'category' => 'required|string',
                'overview' => 'nullable|string',
            ]);
        } elseif ($this->step === 2) {
            $this->validate([
                'job_type' => 'required|string',
                'job_city' => 'nullable|string',
                'default_salary_min' => 'nullable|numeric|min:0',
                'default_salary_max' => 'nullable|numeric|min:0|gte:default_salary_min',
                'description' => 'nullable|string',
                'salary_type' => 'nullable|string',
                'additional_perks' => 'nullable|array',
                'joining_fee_required' => 'nullable|boolean',
            ]);
        } elseif ($this->step === 3) {
            $this->validate([
                'minimum_education' => 'nullable|string',
                'english_level' => 'nullable|string',
                'min_experience_years' => 'nullable|string',
                'max_experience_years' => 'nullable|string',
                'gender_preference' => 'nullable|string',
                'interview_information' => 'nullable|array',
                'default_screening_questions.*.question' => 'required|string',
                'default_screening_questions.*.type' => 'required|in:text,textarea,select,radio,checkbox,file',
                'default_screening_questions.*.options' => 'required_if:default_screening_questions.*.type,select,radio,checkbox',
            ]);
        }

        if ($this->step < 4) {
            $this->step++;
        }
    }

    public function goBack()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function addQuestion()
    {
        $this->default_screening_questions[] = [
            'id' => uniqid('q_'),
            'question' => '',
            'type' => 'text',
            'required' => 1,
            'options' => '',
            'conditional_parent' => '',
            'conditional_value' => ''
        ];
    }

    public function removeQuestion($index)
    {
        unset($this->default_screening_questions[$index]);
        $this->default_screening_questions = array_values($this->default_screening_questions);
    }

    public function store()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
        ]);

        JobTemplate::create([
            'title' => $this->title,
            'category' => $this->category,
            'overview' => $this->overview,
            'description' => $this->description,
            'job_type' => $this->job_type,
            'default_salary_min' => $this->default_salary_min,
            'default_salary_max' => $this->default_salary_max,
            'distance' => $this->distance,
            'job_city' => $this->job_city,
            'default_screening_questions' => $this->default_screening_questions,
            'minimum_education' => $this->minimum_education,
            'english_level' => $this->english_level,
            'min_experience_years' => $this->min_experience_years,
            'max_experience_years' => $this->max_experience_years,
            'gender_preference' => $this->gender_preference,
            'interview_information' => $this->interview_information,
            'additional_perks' => $this->additional_perks,
            'joining_fee_required' => $this->joining_fee_required,
            'salary_type' => $this->salary_type,
            'is_active' => true,
        ]);

        session()->flash('message', 'Job Template Created Successfully.');
        return redirect()->route('admin.job-templates');
    }

    public function update()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
        ]);

        $template = JobTemplate::findOrFail($this->templateId);
        $template->update([
            'title' => $this->title,
            'category' => $this->category,
            'overview' => $this->overview,
            'description' => $this->description,
            'job_type' => $this->job_type,
            'default_salary_min' => $this->default_salary_min,
            'default_salary_max' => $this->default_salary_max,
            'distance' => $this->distance,
            'job_city' => $this->job_city,
            'default_screening_questions' => $this->default_screening_questions,
            'minimum_education' => $this->minimum_education,
            'english_level' => $this->english_level,
            'min_experience_years' => $this->min_experience_years,
            'max_experience_years' => $this->max_experience_years,
            'gender_preference' => $this->gender_preference,
            'interview_information' => $this->interview_information,
            'additional_perks' => $this->additional_perks,
            'joining_fee_required' => $this->joining_fee_required,
            'salary_type' => $this->salary_type,
        ]);

        session()->flash('message', 'Job Template Updated Successfully.');
        return redirect()->route('admin.job-templates');
    }

    public function render()
    {
        return view('livewire.admin.job-settings.job-template-form-component', [
            'jobCategories' => JobCategory::where('is_active', true)->orderBy('name')->get()
        ])->layout('layouts.app', [
            'panelName' => 'Admin Panel',
            'pageTitle' => $this->isEditMode ? 'Edit Job Template' : 'Create Job Template',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}

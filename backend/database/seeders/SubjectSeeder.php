<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $subjects = [
            ["name" => "Intelegentni Sistemi"],
            ["name" => "Izbrani Algoritmi Kombinatorike"],
            ["name" => "Jezikovne Tehnologije"],
            ["name" => "Povezljivi Sistemi in Intelegentne Storitve"],
            ["name" => "Računalniška Obdelava Signalov in Slik"],

        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}

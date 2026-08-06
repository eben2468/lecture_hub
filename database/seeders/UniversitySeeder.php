<?php

namespace Database\Seeders;

use Core\QueryBuilder;

class UniversitySeeder
{
    public function run(): void
    {
        // 1. University of Lagos
        if (!QueryBuilder::table('universities')->where('code', '=', 'UNILAG')->exists()) {
            $univId = QueryBuilder::table('universities')->insert([
                'name'    => 'University of Lagos',
                'code'    => 'UNILAG',
                'domain'  => 'unilag.edu.ng',
                'city'    => 'Akoka, Lagos',
                'state'   => 'Lagos',
                'status'  => 'active',
            ]);

            $facId = QueryBuilder::table('faculties')->insert([
                'university_id' => $univId,
                'name'          => 'Faculty of Science',
                'code'          => 'FSC',
                'dean_name'     => 'Prof. Elijah Johnson',
            ]);

            QueryBuilder::table('departments')->insert([
                'faculty_id' => $facId,
                'name'       => 'Department of Computer Sciences',
                'code'       => 'CSC',
                'hod_name'   => 'Dr. A. O. Adebayo',
            ]);
            QueryBuilder::table('departments')->insert([
                'faculty_id' => $facId,
                'name'       => 'Department of Mathematics',
                'code'       => 'MTH',
                'hod_name'   => 'Dr. K. A. Yusuf',
            ]);
        }

        // 2. Valley View University (Ghana)
        if (!QueryBuilder::table('universities')->where('code', '=', 'VVU')->exists()) {
            $univId = QueryBuilder::table('universities')->insert([
                'name'    => 'Valley View University',
                'code'    => 'VVU',
                'domain'  => 'vvu.edu.gh',
                'city'    => 'Oyibi, Accra',
                'state'   => 'Greater Accra',
                'country' => 'Ghana',
                'status'  => 'active',
            ]);

            $facId = QueryBuilder::table('faculties')->insert([
                'university_id' => $univId,
                'name'          => 'School of Science and Technology',
                'code'          => 'SST',
                'dean_name'     => 'Dr. Beatrice Boateng',
            ]);

            QueryBuilder::table('departments')->insert([
                'faculty_id' => $facId,
                'name'       => 'Department of Computer Science',
                'code'       => 'CSC',
                'hod_name'   => 'Dr. Emmanuel Asante',
            ]);

            QueryBuilder::table('departments')->insert([
                'faculty_id' => $facId,
                'name'       => 'Department of Information Technology',
                'code'       => 'IT',
                'hod_name'   => 'Mrs. Gladys Mensah',
            ]);

            $busId = QueryBuilder::table('faculties')->insert([
                'university_id' => $univId,
                'name'          => 'School of Business',
                'code'          => 'SOB',
                'dean_name'     => 'Prof. Kwaku Prempeh',
            ]);

            QueryBuilder::table('departments')->insert([
                'faculty_id' => $busId,
                'name'       => 'Department of Accounting and Finance',
                'code'       => 'ACF',
                'hod_name'   => 'Dr. Samuel Osei',
            ]);
        }

        // 3. University of Ghana
        if (!QueryBuilder::table('universities')->where('code', '=', 'UG')->exists()) {
            $univId = QueryBuilder::table('universities')->insert([
                'name'    => 'University of Ghana',
                'code'    => 'UG',
                'domain'  => 'ug.edu.gh',
                'city'    => 'Legon, Accra',
                'state'   => 'Greater Accra',
                'country' => 'Ghana',
                'status'  => 'active',
            ]);

            $facId = QueryBuilder::table('faculties')->insert([
                'university_id' => $univId,
                'name'          => 'College of Basic and Applied Sciences',
                'code'          => 'CBAS',
                'dean_name'     => 'Prof. Boateng Onwona-Agyeman',
            ]);

            QueryBuilder::table('departments')->insert([
                'faculty_id' => $facId,
                'name'       => 'Department of Computer Science',
                'code'       => 'CSC',
                'hod_name'   => 'Dr. Jamal-Deen Abdulai',
            ]);
        }

        // 4. Kwame Nkrumah University of Science and Technology
        if (!QueryBuilder::table('universities')->where('code', '=', 'KNUST')->exists()) {
            $univId = QueryBuilder::table('universities')->insert([
                'name'    => 'Kwame Nkrumah University of Science and Technology',
                'code'    => 'KNUST',
                'domain'  => 'knust.edu.gh',
                'city'    => 'Kumasi',
                'state'   => 'Ashanti Region',
                'country' => 'Ghana',
                'status'  => 'active',
            ]);

            $facId = QueryBuilder::table('faculties')->insert([
                'university_id' => $univId,
                'name'          => 'College of Science',
                'code'          => 'COS',
                'dean_name'     => 'Prof. Leonard Amekudzi',
            ]);

            QueryBuilder::table('departments')->insert([
                'faculty_id' => $facId,
                'name'       => 'Department of Computer Science',
                'code'       => 'CSC',
                'hod_name'   => 'Dr. Michael Asante',
            ]);
        }

        echo "   ✔ Ghanaian & Nigerian Universities, Faculties, & Departments Seeded.\n";
    }
}

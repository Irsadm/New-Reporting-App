<?php

use Phinx\Seed\AbstractSeed;

class ItemsSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {

            $data[] = [
                'name'       =>  'Membaca',
                'description'=>  'Membaca buku pelajaran',
                'group_id'   =>  '1',
                'creator'   =>  '1',
                'start_date' =>  '2017-06-9',
                'recurrent'  =>  'Harian',
            ];

            $data[] = [
                'name'       =>  'Upacara',
                'description'=>  'Upacara bendera Hari Senin',
                'recurrent'  =>  'Mingguan',
                'start_date' =>  '2017-06-4',
                'creator'   =>  '1',
                'group_id'   =>  '1',
            ];

            $data[] = [
                'name'       =>  'Tugas Bulanan',
                'start_date' =>  '2017-05-8',
                'start_date' =>  '2017-06-4',
                'recurrent'  =>  'Bulanan',
                'creator'   =>  '1',
                'group_id'   =>  '1',
            ];

            $this->insert('items', $data);
    }
}

<?php
  
namespace Database\Seeders;
  
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
  
class CreateUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
               'firstName'=>'superAdmin User',
               'lastName'=>'lsname',
               'email'=>'superadmin@itsolonstuff.com',
               'picture'=>'admin@itsolutionstu',
               'pictureName'=>'admin@itsolutionstu',
               'phone'=>'123456',
               'role'=>1,
               'password'=> bcrypt('123456'),
            ],
            [
                'firstName'=>'Admin User',
                'lastName'=>'lsname',
                'email'=>'admin@itsolutituff.com',
                'picture'=>'admin@itsolutionstu',
                'pictureName'=>'admin@itsolutionstu',
                'phone'=>'123456',
                'role'=>1,
                'password'=> bcrypt('123456'),
            ],
            [
                'firstName'=>'User',
                'lastName'=>'lsname',
                'email'=>'user@itsolutionstf.com',
                'picture'=>'admin@itsolutionstu',
                'pictureName'=>'admin@itsolutionstu',
                'phone'=>'123456',
                'role'=>1,
                'password'=> bcrypt('123456'),
            ],
        ];
    
        foreach ($users as $key => $user) {
            User::create($user);
        }
    }
}
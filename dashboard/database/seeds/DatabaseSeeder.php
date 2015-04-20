<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Project;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();
		$this->call('ProjectTableSeeder');
	}

}

class ProjectTableSeeder extends Seeder {
    public function run()
    {
        DB::table('projects')->delete();
        Project::create([
            'name' => 'Test project',
            'token' =>'e3bc4100330c35722740fb8c6f5abddc'
        ]);
    }
}

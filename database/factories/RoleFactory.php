<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        $nama = fake()->unique()->words(2, true);
        return [
            'nama'      => ucwords($nama),
            'slug'      => Str::slug($nama),
            'deskripsi' => fake()->sentence(),
        ];
    }
}

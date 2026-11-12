<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCategoryUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_category_with_image()
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $file = UploadedFile::fake()->image('category.jpg', 600, 600);

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Aquariophilie Marine',
            'description' => 'Tout pour les aquariums récifaux',
            'image' => $file,
        ]);

        $response->assertRedirect(route('admin.categories'));
        $category = Category::where('name', 'Aquariophilie Marine')->first();
        $this->assertNotNull($category);
        $this->assertNotNull($category->image);
        Storage::disk('public')->assertExists($category->image);
    }

    public function test_admin_can_update_category_with_new_image()
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $oldFile = UploadedFile::fake()->image('old.jpg');
        $path = $oldFile->store('categories', 'public');

        $category = Category::create([
            'name' => 'Plantes Aquatiques',
            'slug' => 'plantes-aquatiques',
            'image' => $path,
        ]);

        $newFile = UploadedFile::fake()->image('new.jpg');

        $response = $this->actingAs($admin)->post(route('admin.categories.update', $category->id), [
            '_method' => 'PUT',
            'name' => 'Plantes Aquatiques & Mousses',
            'image' => $newFile,
        ]);

        $response->assertRedirect(route('admin.categories'));
        $category->refresh();
        $this->assertEquals('Plantes Aquatiques & Mousses', $category->name);
        Storage::disk('public')->assertExists($category->image);
        Storage::disk('public')->assertMissing($path);
    }
}


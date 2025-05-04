<?php

namespace HamzaEjaz\SearchableScope\Tests;

use HamzaEjaz\SearchableScope\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SearchableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });
    }

    public function test_basic_search()
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $results = User::search('john')->get();
        $this->assertCount(1, $results);
        $this->assertEquals($user->id, $results->first()->id);
    }

    public function test_search_with_relations()
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $post = Post::create([
            'user_id' => $user->id,
            'title' => 'My First Post',
            'content' => 'This is my first post content'
        ]);

        // Debug: Check if post was created
        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'title' => 'My First Post'
        ]);

        // Debug: Print the SQL query
        DB::enableQueryLog();
        $query = User::search('first', [], ['posts' => ['title']]);
        $results = $query->get();
        $queries = DB::getQueryLog();
        print_r($queries);

        $this->assertCount(1, $results, 'Expected to find one user with a post containing "first"');
        if ($results->isNotEmpty()) {
            $this->assertEquals($user->id, $results->first()->id);
        }
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('posts');
        Schema::dropIfExists('users');
        parent::tearDown();
    }
}

class User extends Model
{
    use Searchable;

    protected $fillable = ['name', 'email'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

class Post extends Model
{
    protected $fillable = ['title', 'content', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 
<?php

namespace Tests\Feature;

use App\Models\User;
use AssignPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use PermissionSeeder;
use RoleSeeder;
use Tests\TestCase;
use UserSeeder;

class ChatRoomTest extends TestCase
{
    use RefreshDatabase;

    public function setUp() : void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);
        $this->seed(AssignPermissionSeeder::class);
        $this->seed(UserSeeder::class);
    }

    /**
     * @test
     */
    public function user_can_create_chat_room()
    {
        $users = User::select('name', 'id', 'uuid')->take(3)->get();

        $this->actingAs($users->first());

        $this->postJson(route('chat.create-room'), array(
            'name' => '',
            'members' => []
        ))->assertStatus(422);

        $response = $this->postJson(route('chat.create-room'), array(
            'name' => 'First Room',
            // 'members' => $users->skip(1)->all()
            'members' => []
        ))->assertStatus(200);

        $this->assertDatabaseCount('chat_rooms', 1);
        $this->assertDatabaseCount('chat_room_members', 1);
    }

    /**
     * @test
     */
    public function only_admin_can_create_public_chat_room()
    {
        $user = User::select('name', 'id', 'uuid')->skip(1)->first();

        $this->actingAs($user);

        $this->postJson(route('chat.create-room'), array(
            'name' => 'Public Group',
            'members' => [],
            'is_public_group' => true
        ))->assertStatus(200);

        $this->assertDatabaseCount('chat_rooms', 1);
        $this->assertDatabaseCount('chat_room_members', 1);
    }

    /**
     * @test
     */
    public function admin_can_create_public_chat_room()
    {
        $user_count = User::count();
        $user = User::select('name', 'id', 'uuid')->first();

        $this->actingAs($user);

        $response = $this->postJson(route('chat.create-room'), array(
            'name' => 'First Room',
            'members' => [],
            'is_public_group' => true
        ))->assertStatus(200);

        $this->assertDatabaseCount('chat_rooms', 1);
        $this->assertDatabaseCount('chat_room_members', $user_count);
    }

    /**
     * @test
     */
    public function only_admin_can_sync_members_of_public_chat_room()
    {
        $user = User::select('name', 'id', 'uuid')->skip(1)->first();

        $this->actingAs($user);

        $response = $this->postJson(route('chat.create-room'), array(
            'name' => 'First Room',
            'members' => []
        ))->assertStatus(200);

        $chat_room_uuid = $this->getResponseContent($response, 'uuid');

        $response = $this->postJson(route('chat.sync-member', array('uuid' => $chat_room_uuid)))->assertStatus(403);
    }

    /**
     * @test
     */
    public function admin_can_sync_members_of_public_chat_room()
    {
        $user_count = User::count();
        $user = User::select('name', 'id', 'uuid')->first();

        $this->actingAs($user);

        $response = $this->postJson(route('chat.create-room'), array(
            'name' => 'First Room',
            'members' => [],
            'is_public_group' => true
        ))->assertStatus(200);

        $this->assertDatabaseCount('chat_room_members', $user_count);

        $chat_room_uuid = $this->getResponseContent($response, 'uuid');

        $response = $this->postJson(route('chat.sync-member', array('uuid' => $chat_room_uuid)))->assertStatus(200);

        $this->assertDatabaseCount('chat_room_members', $user_count);
    }

    /**
     * @test
     */
    public function user_can_mention_member_to_chat_room()
    {
        $user = User::select('name', 'id', 'uuid')->first();

        $this->actingAs($user);

        $response = $this->postJson(route('chat.create-room'), array(
            'name' => 'First Room',
            'members' => [],
            'is_public_group' => true
        ))->assertStatus(200);

        $chat_room_uuid = $this->getResponseContent($response, 'uuid');

        $response = $this->getJson(route('chat.list-member', ['uuid' => $chat_room_uuid, 'q' => 'Marry', 'mention' => true]))->assertStatus(200);
    }

    /**
     * @test
     */
    public function user_can_add_member_to_chat_room()
    {
        $users = User::select('name', 'id', 'uuid')->take(5)->get();

        $this->actingAs($users->first());

        $response = $this->postJson(route('chat.create-room'), array(
            'name' => 'First Room',
            'members' => $users->skip(1)->take(2)->all()
        ))->assertStatus(200);

        $chat_room_uuid = $this->getResponseContent($response, 'uuid');

        $response = $this->postJson(route('chat.add-member', ['uuid' => $chat_room_uuid]), array(
            'members' => $users->skip(1)->all()
        ))->assertStatus(200);

        $this->assertDatabaseCount('chat_room_members', 5);
    }

    /**
     * @test
     */
    public function user_can_remove_member_to_chat_room()
    {
        $users = User::select('name', 'id', 'uuid')->take(5)->get();

        $this->actingAs($users->first());

        $response = $this->postJson(route('chat.create-room'), array(
            'name' => 'First Room',
            'members' => $users->skip(1)->take(2)->all()
        ))->assertStatus(200);

        $chat_room_uuid = $this->getResponseContent($response, 'uuid');

        $response = $this->deleteJson(route('chat.remove-member', ['uuid' => $chat_room_uuid]), array(
            'members' => $users->skip(1)->take(1)->all()
        ))->assertStatus(200);
    }

    private function getResponseContent($response, $body)
    {
        $content = json_decode($response->getContent(), true);
        return Arr::get($content, $body);
    }
}

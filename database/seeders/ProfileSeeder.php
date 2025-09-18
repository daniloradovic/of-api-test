<?php

namespace Database\Seeders;

use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::table('profile_scrapes')->delete();
        DB::table('profiles')->delete();

        $profiles = [
            [
                'username' => 'bella_rosemarie',
                'name' => 'Bella Rose Marie',
                'bio' => '🌹 Your favorite girl next door 🌹 Custom content available • Daily posts • VIP treatment for subscribers',
                'avatar_url' => 'https://example.com/avatars/bella_rosemarie.jpg',
                'cover_url' => 'https://example.com/covers/bella_rosemarie.jpg',
                'likes_count' => 2_850_000, // High likes - should scrape daily
                'posts_count' => 1_245,
                'followers_count' => 850_000,
                'following_count' => 2_100,
                'is_verified' => true,
                'is_online' => true,
                'location' => 'Los Angeles, CA',
                'joined_date' => '2020-03-15',
                'last_scraped_at' => now()->subHours(12), // Recently scraped
            ],
            [
                'username' => 'fitness_goddess_maya',
                'name' => 'Maya Fitness',
                'bio' => '💪 Fitness model & trainer 💪 Yoga enthusiast • Healthy lifestyle content • Personal training available',
                'avatar_url' => 'https://example.com/avatars/fitness_goddess_maya.jpg',
                'cover_url' => 'https://example.com/covers/fitness_goddess_maya.jpg',
                'likes_count' => 650_000, // Lower likes - should scrape every 72h
                'posts_count' => 3_890,
                'followers_count' => 425_000,
                'following_count' => 1_200,
                'is_verified' => false,
                'is_online' => false,
                'location' => 'Miami, FL',
                'joined_date' => '2021-07-22',
                'last_scraped_at' => now()->subHours(48), // Needs scraping soon
            ],
            [
                'username' => 'artistic_luna',
                'name' => 'Luna Art',
                'bio' => '🎨 Digital artist & content creator 🎨 Anime lover • Commission work open • Supporting independent artists',
                'avatar_url' => 'https://example.com/avatars/artistic_luna.jpg',
                'cover_url' => 'https://example.com/covers/artistic_luna.jpg',
                'likes_count' => 85_000,
                'posts_count' => 567,
                'followers_count' => 45_000,
                'following_count' => 890,
                'is_verified' => false,
                'is_online' => true,
                'location' => 'Seattle, WA',
                'joined_date' => '2022-11-10',
                'last_scraped_at' => now()->subDays(4), // Needs scraping
            ],
            [
                'username' => 'travel_adventures_sam',
                'name' => 'Samantha Explorer',
                'bio' => '✈️ Travel blogger & photographer ✈️ 50+ countries visited • Adventure seeker • Living my best life',
                'avatar_url' => 'https://example.com/avatars/travel_adventures_sam.jpg',
                'cover_url' => 'https://example.com/covers/travel_adventures_sam.jpg',
                'likes_count' => 1_250_000, // High likes - daily scraping
                'posts_count' => 2_100,
                'followers_count' => 680_000,
                'following_count' => 3_500,
                'is_verified' => true,
                'is_online' => false,
                'location' => 'Barcelona, Spain',
                'joined_date' => '2019-09-05',
                'last_scraped_at' => now()->subHours(18),
            ],
            [
                'username' => 'chef_isabella_gourmet',
                'name' => 'Isabella Gourmet',
                'bio' => '👩‍🍳 Professional chef & food stylist 👩‍🍳 Michelin restaurant experience • Cooking tutorials • Recipe creator',
                'avatar_url' => 'https://example.com/avatars/chef_isabella_gourmet.jpg',
                'cover_url' => 'https://example.com/covers/chef_isabella_gourmet.jpg',
                'likes_count' => 320_000,
                'posts_count' => 1_890,
                'followers_count' => 180_000,
                'following_count' => 450,
                'is_verified' => false,
                'is_online' => true,
                'location' => 'New York, NY',
                'joined_date' => '2021-02-14',
                'last_scraped_at' => null, // Never scraped - will be high priority
            ],
            [
                'username' => 'tech_savvy_alex',
                'name' => 'Alex Tech',
                'bio' => '💻 Software engineer & tech reviewer 💻 Latest gadgets • Programming tutorials • Tech news and insights',
                'avatar_url' => 'https://example.com/avatars/tech_savvy_alex.jpg',
                'cover_url' => 'https://example.com/covers/tech_savvy_alex.jpg',
                'likes_count' => 95_000,
                'posts_count' => 743,
                'followers_count' => 65_000,
                'following_count' => 1_100,
                'is_verified' => false,
                'is_online' => false,
                'location' => 'San Francisco, CA',
                'joined_date' => '2023-01-20',
                'last_scraped_at' => now()->subDays(2),
            ],
            [
                'username' => 'music_producer_jay',
                'name' => 'Jay Beats',
                'bio' => '🎵 Music producer & DJ 🎵 Electronic music specialist • Studio sessions • Remix and original tracks',
                'avatar_url' => 'https://example.com/avatars/music_producer_jay.jpg',
                'cover_url' => 'https://example.com/covers/music_producer_jay.jpg',
                'likes_count' => 2_100_000, // High likes - daily scraping
                'posts_count' => 890,
                'followers_count' => 1_200_000,
                'following_count' => 850,
                'is_verified' => true,
                'is_online' => true,
                'location' => 'Berlin, Germany',
                'joined_date' => '2020-08-11',
                'last_scraped_at' => now()->subHours(30), // Needs daily scraping
            ],
            [
                'username' => 'nature_photographer_emma',
                'name' => 'Emma Nature',
                'bio' => '📸 Wildlife photographer & conservationist 📸 National Geographic contributor • Protecting our planet • Nature education',
                'avatar_url' => 'https://example.com/avatars/nature_photographer_emma.jpg',
                'cover_url' => 'https://example.com/covers/nature_photographer_emma.jpg',
                'likes_count' => 480_000,
                'posts_count' => 1_560,
                'followers_count' => 295_000,
                'following_count' => 780,
                'is_verified' => false,
                'is_online' => false,
                'location' => 'Vancouver, Canada',
                'joined_date' => '2021-05-30',
                'last_scraped_at' => now()->subDays(1),
            ],
            [
                'username' => 'fashion_stylist_zara',
                'name' => 'Zara Style',
                'bio' => '👗 Fashion stylist & trendsetter 👗 Paris Fashion Week • Personal styling services • Sustainable fashion advocate',
                'avatar_url' => 'https://example.com/avatars/fashion_stylist_zara.jpg',
                'cover_url' => 'https://example.com/covers/fashion_stylist_zara.jpg',
                'likes_count' => 1_750_000, // High likes - daily scraping
                'posts_count' => 2_890,
                'followers_count' => 920_000,
                'following_count' => 1_500,
                'is_verified' => true,
                'is_online' => true,
                'location' => 'Paris, France',
                'joined_date' => '2019-12-03',
                'last_scraped_at' => now()->subHours(26), // Needs daily scraping
            ],
            [
                'username' => 'mindfulness_coach_zen',
                'name' => 'Zen Mindfulness',
                'bio' => '🧘‍♀️ Mindfulness coach & meditation teacher 🧘‍♀️ Mental health advocate • Guided meditation • Inner peace journey',
                'avatar_url' => 'https://example.com/avatars/mindfulness_coach_zen.jpg',
                'cover_url' => 'https://example.com/covers/mindfulness_coach_zen.jpg',
                'likes_count' => 125_000,
                'posts_count' => 456,
                'followers_count' => 85_000,
                'following_count' => 340,
                'is_verified' => false,
                'is_online' => false,
                'location' => 'Austin, TX',
                'joined_date' => '2022-04-18',
                'last_scraped_at' => now()->subDays(5), // Needs scraping
            ],
        ];

        foreach ($profiles as $profileData) {
            Profile::create($profileData);
        }

        $this->command->info('✅ Created ' . count($profiles) . ' sample profiles');
        $this->command->info('   📊 ' . collect($profiles)->where('likes_count', '>', 100000)->count() . ' profiles with >100k likes (daily scraping)');
        $this->command->info('   📊 ' . collect($profiles)->where('likes_count', '<=', 100000)->count() . ' profiles with <=100k likes (72h scraping)');
        $this->command->info('   📊 ' . collect($profiles)->whereNull('last_scraped_at')->count() . ' profiles never scraped (high priority)');
    }
}
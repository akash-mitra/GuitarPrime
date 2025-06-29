<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Database\Seeder;

class GuitarPrimeDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating Guitar Prime themes and courses...');

        // JSON data from the provided document
        $data = [
            'themes' => [
                [
                    'name' => 'Fingerstyle Essentials',
                    'courses' => [
                        [
                            'name' => 'Fingerpicking Basics',
                            'description' => 'An introduction to fingerpicking technique using simple Hindi and English melodies. Ideal for complete beginners.',
                        ],
                        [
                            'name' => 'Melodic Fingerstyle Tunes',
                            'description' => 'Learn to play melody and chords together in popular Bollywood songs with basic fingerstyle arrangements.',
                        ],
                        [
                            'name' => 'Fingerstyle Rhythms & Patterns',
                            'description' => 'Explore common rhythmic patterns used in Indian fingerstyle playing. Focus on groove, timing, and musicality.',
                        ],
                    ],
                ],
                [
                    'name' => 'Fingerstyle Advanced',
                    'courses' => [
                        [
                            'name' => 'Percussive & Modern Fingerstyle',
                            'description' => 'Advanced fingerstyle techniques such as body tapping, slap harmonics, and syncopated grooves using Bollywood melodies.',
                        ],
                        [
                            'name' => 'Classical & Spanish Pieces',
                            'description' => 'Play classical and Spanish-inspired pieces on acoustic guitar with focus on arpeggios, tremolo, and dynamics.',
                        ],
                        [
                            'name' => 'Bollywood Fingerstyle Arrangements',
                            'description' => 'Complex full-length arrangements of iconic Bollywood tracks with melody, harmony, and rhythm integrated.',
                        ],
                    ],
                ],
                [
                    'name' => 'Acoustic Unplugged',
                    'courses' => [
                        [
                            'name' => 'Open Chords & Strumming',
                            'description' => 'Master essential open chords and strumming patterns using easy Hindi songs and English pop classics.',
                        ],
                        [
                            'name' => 'Bollywood Unplugged Favorites',
                            'description' => 'Play unplugged versions of popular Bollywood songs, ideal for singing along or solo performance.',
                        ],
                        [
                            'name' => 'Campfire & Sing-along Hits',
                            'description' => 'Perfect for group settings—learn high-energy, easy-to-sing songs using basic chord progressions and strums.',
                        ],
                    ],
                ],
                [
                    'name' => 'Flamenco & Latin',
                    'courses' => [
                        [
                            'name' => 'Introduction to Flamenco Guitar',
                            'description' => 'Learn rasgueado, golpe, and palmas techniques to bring Flamenco flair into Indian melodies.',
                        ],
                        [
                            'name' => 'Latin Rhythms & Songs',
                            'description' => 'Explore Bossa Nova, Rumba, and Salsa grooves and apply them to fusion-style Bollywood covers.',
                        ],
                    ],
                ],
                [
                    'name' => 'Electric Guitar Rock & Solos',
                    'courses' => [
                        [
                            'name' => 'Rock Guitar Foundations',
                            'description' => 'Core rock techniques—power chords, palm muting, distortion—applied to Bollywood rock anthems.',
                        ],
                        [
                            'name' => 'Iconic Riffs & Solos',
                            'description' => 'Break down and play famous riffs and solos from Hindi and English rock tracks using electric guitar techniques.',
                        ],
                        [
                            'name' => 'Lead Guitar & Advanced Solos',
                            'description' => 'Learn melodic phrasing, legato, bending and tapping using solos from films and indie rock.',
                        ],
                    ],
                ],
                [
                    'name' => 'Indian Fusion Guitar',
                    'courses' => [
                        [
                            'name' => 'Raga Basics on Guitar',
                            'description' => 'Translate Hindustani ragas to guitar using fretboard patterns and Indian ornamentation techniques.',
                        ],
                        [
                            'name' => 'Bollywood & Folk Fusion',
                            'description' => 'Use guitar to blend folk instruments like tabla, dholak, and flute with Indian pop/folk melodies.',
                        ],
                        [
                            'name' => 'Devotional & Spiritual Guitar',
                            'description' => 'Learn bhajan-style playing and spiritual fusion compositions using fingerstyle and soft strumming.',
                        ],
                    ],
                ],
                [
                    'name' => 'Devotional Guitar',
                    'courses' => [
                        [
                            'name' => 'Bhajans & Aartis',
                            'description' => 'Simple devotional songs played with chords and arpeggios for worship and meditation settings.',
                        ],
                        [
                            'name' => 'Sufi & Spiritual Songs',
                            'description' => 'Learn powerful Sufi compositions and understand modal frameworks and soulful chord progressions.',
                        ],
                        [
                            'name' => 'Patriotic & Inspirational',
                            'description' => "Play motivational tracks like 'Ae Watan', 'Maa Tujhe Salaam' using modern acoustic and electric stylings.",
                        ],
                    ],
                ],
                [
                    'name' => 'Indie & Contemporary Hits',
                    'courses' => [
                        [
                            'name' => 'Viral Indie Pop',
                            'description' => 'Learn trending Indian indie songs by artists like Prateek Kuhad, Anuv Jain, and others in singer-songwriter format.',
                        ],
                        [
                            'name' => 'OTT & Film Chartbusters',
                            'description' => 'Explore Netflix/Prime/OTT releases and hit movie songs with modern acoustic or fingerstyle arrangements.',
                        ],
                        [
                            'name' => 'Regional & Folk Hits',
                            'description' => 'Guitar arrangements of extremely popular regional songs in Punjabi, Bengali, and South Indian languages.',
                        ],
                    ],
                ],
                [
                    'name' => "Kids' Song Covers",
                    'courses' => [
                        [
                            'name' => 'Nursery Rhymes & Basics',
                            'description' => 'Super simple versions of nursery rhymes and melodies using single notes or open-string chords.',
                        ],
                        [
                            'name' => "Kids' Movie & Disney Songs",
                            'description' => "Play favorites like 'Let It Go' or 'Hakuna Matata' using easy chords and engaging rhythms for young learners.",
                        ],
                    ],
                ],
                [
                    'name' => 'Guitar for Singers',
                    'courses' => [
                        [
                            'name' => 'Strumming & Singing Essentials',
                            'description' => 'Coordinate singing with rhythm guitar by learning time-feel, strumming accuracy, and dynamic control.',
                        ],
                        [
                            'name' => 'Changing Keys & Capo Use',
                            'description' => 'Learn how to transpose and use capo for different vocal ranges using Bollywood and pop examples.',
                        ],
                        [
                            'name' => 'Performance & Accompaniment Techniques',
                            'description' => 'Build live performance confidence with fingerstyle fills, intro/outro crafting, and guitar-vocal arrangements.',
                        ],
                    ],
                ],
            ],
        ];

        // Create admin user to assign as coach for these courses
        $adminCoach = User::firstOrCreate(
            ['email' => 'admin@guitarprime.com'],
            [
                'name' => 'Guitar Prime Admin',
                'role' => 'admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        foreach ($data['themes'] as $themeData) {
            $this->command->info("Creating theme: {$themeData['name']}");

            $theme = Theme::create([
                'name' => $themeData['name'],
                'description' => "Collection of courses focused on {$themeData['name']} guitar techniques and styles.",
                'cover_image' => $themeData['cover_image'] ?? 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=800&q=80',
            ]);

            foreach ($themeData['courses'] as $courseData) {
                $this->command->info("  Creating course: {$courseData['name']}");

                Course::create([
                    'theme_id' => $theme->id,
                    'coach_id' => $adminCoach->id,
                    'title' => $courseData['name'],
                    'description' => $courseData['description'],
                    'is_approved' => true,
                    'cover_image' => $courseData['cover_image'] ?? 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=800&q=80',
                ]);
            }
        }

        $this->command->info('Guitar Prime themes and courses created successfully!');
    }
}

<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * The three posters from the 2023 FYP dashboard slideshow
 * (tag v1-fyp, resources/views/components/welcome.blade.php, public/images/1-3.jpg).
 * Safe to run again: it matches on the stored image path.
 */
class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $slides = [
            [
                'source' => 'images/1.jpg',
                'image_path' => 'announcements/fyp-1-blood-donation-kpj-kluang.jpg',
                'title' => 'Blood Donation Campaign at KPJ Kluang',
                'body' => 'Thursday 23 June 2022, 9.00am to 12.30pm, Level G, KPJ Kluang Specialist Hospital, with HEBHK and Cryocord. Donating blood is an act of solidarity. Join the effort and save lives. Enquiries: +607-771 8999 ext. 106.',
                'published_at' => '2022-06-01 08:00:00',
                'sort_order' => 1,
            ],
            [
                'source' => 'images/2.jpg',
                'image_path' => 'announcements/fyp-2-blood-donation-kluang-pkmp.jpg',
                'title' => "Let's Donate Blood · Kluang PKMP",
                'body' => 'Sunday 30 October 2022, 9am to 2pm at Kluang PKMP, organised by Persatuan Kebajikan Mesra Prihatin Kluang in collaboration with HEBHK. Every drop of blood is a gift of life.',
                'published_at' => '2022-10-01 08:00:00',
                'sort_order' => 2,
            ],
            [
                'source' => 'images/3.jpg',
                'image_path' => 'announcements/fyp-3-farmasi-myubat.jpg',
                'title' => 'Perkhidmatan Nilai Tambah Farmasi · MyUBAT',
                'body' => 'Ambil bekalan ubat susulan tanpa menunggu lama: Ubat Melalui Pos, Locker4U, WhatsApp & Ambil, Kad Temujanji dan SMS & Ambil. Maklumat lanjut: 07-7787000 samb. 2359 (Unit Farmasi Pesakit Luar, HEBHK).',
                'published_at' => '2023-01-01 08:00:00',
                'sort_order' => 3,
            ],
        ];

        $disk = Storage::disk('public');

        foreach ($slides as $slide) {
            $source = public_path($slide['source']);
            if (! $disk->exists($slide['image_path']) && File::exists($source)) {
                $disk->put($slide['image_path'], File::get($source));
            }

            Announcement::query()->updateOrCreate(
                ['image_path' => $slide['image_path']],
                [
                    'title' => $slide['title'],
                    'body' => $slide['body'],
                    'published_at' => $slide['published_at'],
                    'expires_at' => null,
                    'sort_order' => $slide['sort_order'],
                    'is_active' => true,
                ],
            );
        }
    }
}

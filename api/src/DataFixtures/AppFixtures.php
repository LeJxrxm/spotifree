<?php

namespace App\DataFixtures;

use App\Entity\Album;
use App\Entity\Artiste;
use App\Entity\Musique;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    private function makeUsers(ObjectManager $manager): array
    {

        $usersData = [
            [
                'email' => 'jeremy@test.com',
                'pseudo' => 'Jeremy',
                'roles' => ['ROLE_ADMIN'],
                'password' => 'password'
            ],
            [
                'email' => 'admin@test.com',
                'pseudo' => 'Admin',
                'roles' => ['ROLE_ADMIN'],
                'password' => 'password'
            ],
        ];

        $users = [];

        foreach ($usersData as $data) {
            $user = new User();
            $user->setEmail($data['email']);
            $user->setPseudo($data['pseudo']);
            $user->setRoles($data['roles']);

            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);

            $manager->persist($user);
            $users[] = $user;
        }

        return $users;
    }

    private function makeArtists(ObjectManager $manager): array
    {
        $artistsData = [
            [
                'nom' => 'Pink Floyd',
                'imageUrl' => '/uploads/Pink_Floyd_-_all_members.jpg'
            ],
            [
                'nom' => 'Daft Punk',
                'imageUrl' => '/uploads/daft-punk.jpg'
            ],
        ];

        $artists = [];

        foreach ($artistsData as $data) {
            $artist = new Artiste();
            $artist->setNom($data['nom']);
            $artist->setImageUrl($data['imageUrl']);

            $manager->persist($artist);
            $artists[] = $artist;
        }

        return $artists;
    }

    private function makeAlbums(ObjectManager $manager, array $artists): array
    {
        $getRandomArtist = function () use ($artists) {
            return $artists[array_rand($artists)];
        };

        $albumsData = [
            [
                'titre' => 'The Dark Side of the Moon',
                'coverUrl' => '/uploads/Dark_Side_of_the_Moon.png',
                'artiste' => $getRandomArtist()
            ],
            [
                'titre' => 'Discovery',
                'coverUrl' => '/uploads/Daft_Punk_-_Discovery.png',
                'artiste' => $getRandomArtist()
            ]
        ];

        $albums = [];

        foreach ($albumsData as $data) {
            $album = new Album();
            $album->setTitre($data['titre']);
            $album->setCoverUrl($data['coverUrl']);
            $album->setArtiste($data['artiste']);

            $manager->persist($album);
            $albums[] = $album;
        }

        return $albums;
    }

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $users = $this->makeUsers($manager);

        // 2. Create Artists
        $artists = $this->makeArtists($manager);

        // 3. Create Albums
        $albums = $this->makeAlbums($manager, $artists);

        // 4. Create Musiques
        $tracks1 = [
            'Speak to Me' => 90,
            'Breathe' => 163,
            'On the Run' => 216,
            'Time' => 421,
            'The Great Gig in the Sky' => 276
        ];

        $i = 1;
        foreach ($tracks1 as $title => $duration) {
            $track = new Musique();
            $track->setTitre($title);
            $track->setDuree($duration);
            $track->setAudioUrl('https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3'); // Example audio
            $track->setTrackNumber($i);
            $track->setAlbum($albums[0]);
            $track->setArtiste($artists[0]);
            $track->setUploader($users[0]); // Admin uploaded these
            $manager->persist($track);
            $i++;
        }

        $tracks2 = [
            'One More Time' => 320,
            'Aerodynamic' => 207,
            'Digital Love' => 298,
            'Harder, Better, Faster, Stronger' => 224
        ];

        $j = 1;
        foreach ($tracks2 as $title => $duration) {
            $track = new Musique();
            $track->setTitre($title);
            $track->setDuree($duration);
            $track->setAudioUrl('https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3');
            $track->setTrackNumber($j);
            $track->setAlbum($albums[1]);
            $track->setArtiste($artists[1]);
            $track->setUploader($users[1]); // Regular user uploaded these
            $manager->persist($track);
            $j++;
        }

        $manager->flush();
    }
}

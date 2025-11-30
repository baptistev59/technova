<?php

namespace App\DataFixtures;

use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\ProductImage;
use App\Entity\ProductReview;
use App\Entity\Shop;
use App\Entity\User;
use App\Entity\Vendor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use function random_int;

/**
 * Fixtures de démonstration : créent un admin, un vendeur, un shop
 * et quelques produits cohérents avec les maquettes.
 */
class AppFixtures extends Fixture
{
    public function __construct(
        private SluggerInterface $slugger,
        private UserPasswordHasherInterface $passwordHasher
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        // -- Compte admin pour les tests Postman --
        $admin = (new User())
            ->setEmail('admin@test.fr')
            ->setRoles(['ROLE_ADMIN'])
            ->setFirstname('Admin')
            ->setLastname('TechNova');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, '123456'));
        $manager->persist($admin);

        // -- Vendeur/exploitant principal --
        $vendor = (new Vendor())
            ->setCompanyName('TechNova Partners')
            ->setBusinessId('TNM-001')
            ->setBusinessIdType('SIRET')
            ->setEmail('contact@technova-partners.test')
            ->setPhone('+33 145 000 123')
            ->setWebsite('https://technova-partners.test')
            ->setOwner($admin);
        $manager->persist($vendor);

        // -- Boutique vitrine (utilisée sur les pages Twig) --
        $shop = (new Shop())
            ->setName('TechNova Marketplace')
            ->setSlug($this->slug('TechNova Marketplace'))
            ->setDescription('Boutique officielle proposant une sélection de produits tech lifestyle.')
            ->setLogo('https://placehold.co/200x200?text=TechNova')
            ->setBanner('https://placehold.co/1600x400?text=TechNova+Marketplace')
            ->setContactEmail('hello@technova.test')
            ->setPolicies('Livraison 48h, retours gratuits sous 30 jours.')
            ->setOwner($vendor);
        $manager->persist($shop);

        $categories = [];
        // -- Catégories principales --
        foreach ([
            ['Informatique', 'Ordinateurs et accessoires'],
            ['Smartphones & Tablettes', 'Mobiles, tablettes et accessoires'],
            ['Maison connectée', 'Objets intelligents pour la maison'],
        ] as [$name, $desc]) {
            $category = (new Category())
                ->setName($name)
                ->setSlug($this->slug($name))
                ->setDescription($desc);
            $manager->persist($category);
            $categories[$category->getSlug()] = $category;
        }

        $brands = [];
        // -- Marques fictives --
        foreach ([
            ['NovaTech', 'Solutions premium pour les créateurs.'],
            ['UrbanGears', 'Accessoires mobiles urbains et robustes.'],
            ['HomeSense', 'Gamme d’objets connectés pour la maison.'],
        ] as [$name, $desc]) {
            $brand = (new Brand())
                ->setName($name)
                ->setSlug($this->slug($name))
                ->setDescription($desc)
                ->setLogoUrl('https://placehold.co/200x80?text=' . urlencode($name));
            $manager->persist($brand);
            $brands[$brand->getSlug()] = $brand;
        }

        // -- Catalogue minimal : 3 produits vitrines --
        $products = [
            [
                'name' => 'NovaBook Pro 15',
                'short' => 'Ultrabook 15" aluminium, autonomie 12h.',
                'desc' => 'Écran Retina 15 pouces, processeur Octo-Core, 32 Go RAM, 1 To SSD NVMe.',
                'price' => 1899.00,
                'stock' => 8,
                'category' => 'informatique',
                'brand' => 'novatech',
                'thumbnail' => 'https://images.unsplash.com/photo-1486312338219-ce68d2c6f44d?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'name' => 'UrbanGears AirPods Case',
                'short' => 'Étui de protection silicone et mousqueton.',
                'desc' => 'Porte-clefs résistant pour protéger vos AirPods des chutes du quotidien.',
                'price' => 29.90,
                'stock' => 120,
                'category' => 'smartphones-tablettes',
                'brand' => 'urbangears',
                'thumbnail' => 'https://images.unsplash.com/photo-1587831990711-23ca6441447b?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'name' => 'HomeSense Smart Lamp',
                'short' => 'Lampe connectée multi-ambiances, commande vocale.',
                'desc' => 'Compatible Alexa/Google Home, réglage chaleur et intensité via application.',
                'price' => 159.00,
                'stock' => 35,
                'category' => 'maison-connectee',
                'brand' => 'homesense',
                'thumbnail' => 'https://images.unsplash.com/photo-1481277542470-605612bd2d61?auto=format&fit=crop&w=900&q=80',
            ],
        ];

        foreach ($products as $data) {
            $category = $categories[$data['category']] ?? null;
            $brand = $brands[$data['brand']] ?? null;

            if (!$category || !$brand) {
                continue;
            }

            // Produit de base
            $product = (new Product())
                ->setName($data['name'])
                ->setSlug($this->slug($data['name']))
                ->setShortDescription($data['short'])
                ->setDescription($data['desc'])
                ->setPrice($data['price'])
                ->setStock($data['stock'])
                ->setSku(strtoupper(substr($data['brand'], 0, 3)) . '-' . \random_int(1000, 9999))
                ->setType('standard')
                ->setIsFeatured(true)
                ->setCategory($category)
                ->setBrand($brand)
                ->setShop($shop);

            // Image principale (placeholder Unsplash)
            $image = (new ProductImage())
                ->setUrl($data['thumbnail'])
                ->setAlt($data['name'])
                ->setTitle($data['name'])
                ->setPosition(0)
                ->setIsMain(true)
                ->setProduct($product);

            // Avis court pour donner du réalisme
            $review = (new ProductReview())
                ->setRating(\random_int(4, 5))
                ->setComment('Produit adopté ! Qualité premium et livraison rapide.')
                ->setProduct($product);

            $manager->persist($product);
            $manager->persist($image);
            $manager->persist($review);
        }

        $manager->flush();
    }

    /** Helper pour homogénéiser les slugs. */
    private function slug(string $value): string
    {
        return strtolower($this->slugger->slug($value)->toString());
    }
}

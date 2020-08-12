<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Resolver\CreateCustomerResolver;
use App\Resolver\GetCustomerResolver;
use App\Resolver\UpdateCustomerResolver;
use App\Resolver\UpdateInfoForCustomerResolver;
use App\Resolver\SetNullShippingPriceForAllCustomerResolver;
use App\Traits\Entity\TimestampableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CustomerRepository")
 * @ORM\Table(
 *     name="customers",
 *     uniqueConstraints={
 *       @ORM\UniqueConstraint (columns={"origin_id", "shop_id"})
 *     }
 * )
 *
 */
class Customer
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @var Shop
     * @ORM\ManyToOne(targetEntity="App\Entity\Shop")
     * @ORM\JoinColumn(nullable=false)
     */
    private $shop;

    /**
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Cart", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $currentCart;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Cart", mappedBy="customer")
     */
    private $carts;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=2, nullable=true)
     * @Assert\NotBlank()
     * @Assert\Range(min=0)
     * @Assert\Type(type="numeric")
     */
    private $shippingPrice;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Assert\Length(max=20, allowEmptyString=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, allowEmptyString=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Email()
     * @Assert\Length(max=255, allowEmptyString=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, allowEmptyString=true)
     */
    private $region;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, allowEmptyString=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Assert\Length(max=50, allowEmptyString=true)
     */
    private $zip;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, allowEmptyString=true)
     */
    private $district;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, allowEmptyString=true)
     */
    private $address1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, allowEmptyString=true)
     */
    private $address2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, allowEmptyString=true)
     */
    private $house;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, allowEmptyString=true)
     */
    private $entrance;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, allowEmptyString=true)
     */
    private $floor;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, allowEmptyString=true)
     */
    private $flat;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\AbTestNomenclatureRangePrice",
     *     mappedBy="customer",
     *     orphanRemoval=true,
     *     cascade={"persist"}
     * )
     */
    private $abTestNomenclatureRangePrices;

    /**
     * @ORM\Column(type="integer")
     */
    private $originId;


    private $httpHeaders = [];

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     * @return self
     */
    public function setPhone($phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return self
     */
    public function setName($name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return self
     */
    public function setEmail($email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param mixed $region
     * @return self
     */
    public function setRegion($region): self
    {
        $this->region = $region;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     * @return self
     */
    public function setCity($city): self
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param mixed $zip
     * @return self
     */
    public function setZip($zip): self
    {
        $this->zip = $zip;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * @param mixed $district
     * @return self
     */
    public function setDistrict($district): self
    {
        $this->district = $district;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @param mixed $address1
     * @return self
     */
    public function setAddress1($address1): self
    {
        $this->address1 = $address1;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param mixed $address2
     * @return self
     */
    public function setAddress2($address2): self
    {
        $this->address2 = $address2;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHouse()
    {
        return $this->house;
    }

    /**
     * @param mixed $house
     * @return self
     */
    public function setHouse($house): self
    {
        $this->house = $house;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntrance()
    {
        return $this->entrance;
    }

    /**
     * @param mixed $entrance
     * @return self
     */
    public function setEntrance($entrance): self
    {
        $this->entrance = $entrance;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFloor()
    {
        return $this->floor;
    }

    /**
     * @param mixed $floor
     * @return self
     */
    public function setFloor($floor): self
    {
        $this->floor = $floor;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFlat()
    {
        return $this->flat;
    }

    /**
     * @param mixed $flat
     * @return self
     */
    public function setFlat($flat): self
    {
        $this->flat = $flat;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param mixed $note
     * @return self
     */
    public function setNote($note): self
    {
        $this->note = $note;
        return $this;
    }

    /**
     * End Customer data block.
     */

    public function __construct()
    {
        $this->carts = new ArrayCollection();
        $this->abTestNomenclatureRangePrices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getCurrentCart(): ?Cart
    {
        return $this->currentCart;
    }

    public function setCurrentCart(?Cart $currentCart): self
    {
        $this->currentCart = $currentCart;

        return $this;
    }

    /**
     * @return Collection|Cart[]
     */
    public function getCarts(): Collection
    {
        return $this->carts;
    }

    public function addCart(Cart $cart): self
    {
        if (!$this->carts->contains($cart)) {
            $this->carts[] = $cart;
            $cart->setCustomer($this);
        }

        return $this;
    }

    public function removeCart(Cart $cart): self
    {
        if ($this->carts->contains($cart)) {
            $this->carts->removeElement($cart);
            // set the owning side to null (unless already changed)
            if ($cart->getCustomer() === $this) {
                $cart->setCustomer(null);
            }
        }

        return $this;
    }

    public function clearCarts(): self
    {
        foreach ($this->carts as $cart){
            if ($cart->getCustomer() === $this) {
                $cart->setCustomer(null);
            }
        }
        $this->carts->clear();
        return $this;
    }

    public function getShippingPrice(): ?string
    {
        return $this->shippingPrice;
    }

    public function setShippingPrice(?string $shippingPrice = null): self
    {
        $this->shippingPrice = $shippingPrice;

        return $this;
    }

    /**
     * @return Collection|AbTestNomenclatureRangePrice[]
     */
    public function getAbTestNomenclatureRangePrices(): Collection
    {
        return $this->abTestNomenclatureRangePrices;
    }

    public function addAbTestNomenclatureRangePrice(AbTestNomenclatureRangePrice $abTestNomenclatureRangePrice): self
    {
        if (!$this->abTestNomenclatureRangePrices->contains($abTestNomenclatureRangePrice)) {
            $this->abTestNomenclatureRangePrices[] = $abTestNomenclatureRangePrice;
            $abTestNomenclatureRangePrice->setCustomer($this);
        }

        return $this;
    }

    public function removeAbTestNomenclatureRangePrice(AbTestNomenclatureRangePrice $abTestNomenclatureRangePrice): self
    {
        if ($this->abTestNomenclatureRangePrices->contains($abTestNomenclatureRangePrice)) {
            $this->abTestNomenclatureRangePrices->removeElement($abTestNomenclatureRangePrice);
            // set the owning side to null (unless already changed)
            if ($abTestNomenclatureRangePrice->getCustomer() === $this) {
                $abTestNomenclatureRangePrice->setCustomer(null);
            }
        }

        return $this;
    }

    public function getHttpHeaders(): array
    {
        return $this->httpHeaders;
    }

    public function addHttpHeader(string $name, string $value): self
    {
        $this->httpHeaders[] = [
            'name' => $name,
            'value' => $value,
        ];

        return $this;
    }

    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): self
    {
        $this->shop = $shop;
        return $this;
    }
    public function getOriginId(): ?int
    {
        return $this->originId;
    }

    public function setOriginId(int $originId): self
    {
        $this->originId = $originId;
        return $this;
    }
}

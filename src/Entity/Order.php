<?php

namespace App\Entity;

use App\Constant\OrderStatuses;
use App\Traits\Entity\TimestampableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderRepository")
 * @ORM\Table(name="orders")
 */
class Order
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Template
     * @ORM\ManyToOne(targetEntity="App\Entity\Template", inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $template;

    /**
     * @var Shop
     * @ORM\ManyToOne(targetEntity="App\Entity\Shop", inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $shop;


    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\OrderItem",
     *     mappedBy="order",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove"}
     * )
     */
    private $orderItems;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"read", "write"})
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     */
    private $orderId;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=2)
     * @Groups({"read", "write"})
     * @Assert\NotBlank()
     * @Assert\Type(type="numeric")
     */
    private $itemsPrice;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=2)
     * @Groups({"read", "write"})
     * @Assert\NotBlank()
     * @Assert\Type(type="numeric")
     */
    private $shippingPrice;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=2)
     * @Groups({"read", "write"})
     * @Assert\NotBlank()
     * @Assert\Type(type="numeric")
     */
    private $total;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Groups({"read", "write"})
     * @Assert\NotBlank()
     * @Assert\Length(max=20, allowEmptyString=false)
     */
    private $paymentMethod;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"read", "write"})
     * @Assert\NotBlank()
     * @Assert\Length(max=20)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read", "write"})
     * @Assert\Length(max=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read", "write"})
     * @Assert\Length(max=255)
     */
    private $region;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read", "write"})
     * @Assert\Length(max=255)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"read", "write"})
     * @Assert\Length(max=50)
     */
    private $zip;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read", "write"})
     * @Assert\Length(max=255)
     */
    private $district;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read", "write"})
     * @Assert\Length(max=255)
     */
    private $address1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read", "write"})
     * @Assert\Length(max=255)
     */
    private $address2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read", "write"})
     */
    private $house;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read", "write"})
     * @Assert\Length(max=255)
     */
    private $entrance;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read", "write"})
     * @Assert\Length(max=255)
     */
    private $floor;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read", "write"})
     * @Assert\Length(max=255)
     */
    private $flat;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read", "write"})
     */
    private $note;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read", "write"})
     * @Assert\Length(max=255, allowEmptyString=true)
     */
    private $name;

    /**
     * @ORM\Column(type="json", length=255)
     */
    private $data;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $erpExternalId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read", "write"})
     */
    private $utmCampaign;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read", "write"})
     */
    private $link;


    /**
     * @ORM\Column(type="json")
     */
    private $abTestIds = [];

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
     */
    private $itemsPriceDiscount;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=2)
     */
    private $itemsPriceOrig;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=2, nullable=true)
     */
    private $shippingPriceDiscount;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=2)
     */
    private $shippingPriceOrig;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=2)
     */
    private $totalOrig;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $status = OrderStatuses::CREATED;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $paygErrors = [];

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $abTestPaymentTypeListIndex;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Cart", inversedBy="order", cascade={"persist", "remove"})
     */
    private $cart;

    /**
     * @ORM\Column(type="json")
     */
    private $abTestNomenclatureImageChain = [];

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $visits;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $abTestCartKindItemsIndex;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ErpLog", mappedBy="orders")
     */
    private $erpLogs;

    /**
     * @return mixed
     */
    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
        $this->erpLogs = new ArrayCollection();
    }

    /**
     * @return Collection|OrderItem[]
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): self
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems[] = $orderItem;
            $orderItem->setOrder($this);
        }

        return $this;
    }

    public function clearOrderItems(): self
    {
        foreach ($this->orderItems as $orderItem){
            $this->orderItems->removeElement($orderItem);
        }
        return $this;
    }
    public function removeOrderItem(OrderItem $orderItem): self
    {
        if ($this->orderItems->contains($orderItem)) {
            $this->orderItems->removeElement($orderItem);
            // set the owning side to null (unless already changed)
            if ($orderItem->getOrder() === $this) {
                $orderItem->setOrder(null);
            }
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getErpExternalId()
    {
        return $this->erpExternalId;
    }

    /**
     * @param mixed $erpExternalId
     */
    public function setErpExternalId($erpExternalId): self
    {
        $this->erpExternalId = $erpExternalId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return self
     */
    public function setId($id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param mixed $template
     * @return self
     */
    public function setTemplate($template): self
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return Shop
     */
    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    /**
     * @param Shop $shop
     * @return self
     */
    public function setShop(Shop $shop): self
    {
        $this->shop = $shop;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getItemsPrice()
    {
        return $this->itemsPrice;
    }

    /**
     * @param mixed $itemsPrice
     * @return self
     */
    public function setItemsPrice($itemsPrice): self
    {
        $this->itemsPrice = $itemsPrice;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getShippingPrice()
    {
        return $this->shippingPrice;
    }

    /**
     * @param mixed $shippingPrice
     * @return self
     */
    public function setShippingPrice($shippingPrice): self
    {
        $this->shippingPrice = $shippingPrice;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param mixed $total
     * @return self
     */
    public function setTotal($total): self
    {
        $this->total = $total;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @param mixed $paymentMethod
     * @return self
     */
    public function setPaymentMethod($paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

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
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return self
     */
    public function setData($data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     * @return self
     */
    public function setUpdatedAt($updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param mixed $orderId
     */
    public function setOrderId($orderId): self
    {
        $this->orderId = $orderId;
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
     */
    public function setName($name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getAbTestIds(): ?array
    {
        return $this->abTestIds;
    }

    public function setAbTestIds(array $abTestIds): self
    {
        $this->abTestIds = $abTestIds;

        return $this;
    }

    public function getUtmCampaign(): ?string
    {
        return $this->utmCampaign;
    }

    public function setUtmCampaign(?string $utmCampaign): self
    {
        $this->utmCampaign = $utmCampaign;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getItemsPriceDiscount(): ?string
    {
        return $this->itemsPriceDiscount;
    }

    public function setItemsPriceDiscount(?string $itemsPriceDiscount): self
    {
        $this->itemsPriceDiscount = $itemsPriceDiscount;

        return $this;
    }

    public function getItemsPriceOrig(): ?string
    {
        return $this->itemsPriceOrig;
    }

    public function setItemsPriceOrig(string $itemsPriceOrig): self
    {
        $this->itemsPriceOrig = $itemsPriceOrig;

        return $this;
    }

    public function getShippingPriceDiscount(): ?string
    {
        return $this->shippingPriceDiscount;
    }

    public function setShippingPriceDiscount(?string $shippingPriceDiscount): self
    {
        $this->shippingPriceDiscount = $shippingPriceDiscount;

        return $this;
    }

    public function getShippingPriceOrig(): ?string
    {
        return $this->shippingPriceOrig;
    }

    public function setShippingPriceOrig(string $shippingPriceOrig): self
    {
        $this->shippingPriceOrig = $shippingPriceOrig;

        return $this;
    }

    public function getTotalOrig(): ?string
    {
        return $this->totalOrig;
    }

    public function setTotalOrig(string $totalOrig): self
    {
        $this->totalOrig = $totalOrig;

        return $this;
    }
    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPaygErrors(): ?array
    {
        return $this->paygErrors;
    }

    public function setPaygErrors(?array $paygErrors): self
    {
        $this->paygErrors = $paygErrors;

        return $this;
    }

    public function addPaygError($paygError): self
    {
        $this->paygErrors = array_merge(
            $this->paygErrors ?? [],
            [$paygError]
        );

        return $this;
    }

    public function getAbTestPaymentTypeListIndex(): ?int
    {
        return $this->abTestPaymentTypeListIndex;
    }

    public function setAbTestPaymentTypeListIndex(?int $abTestPaymentTypeListIndex): self
    {
        $this->abTestPaymentTypeListIndex = $abTestPaymentTypeListIndex;

        return $this;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(?Cart $cart): self
    {
        $this->cart = $cart;

        return $this;
    }

    public function getAbTestNomenclatureImageChain(): ?array
    {
        return $this->abTestNomenclatureImageChain;
    }

    public function setAbTestNomenclatureImageChain(array $abTestNomenclatureImageChain): self
    {
        $this->abTestNomenclatureImageChain = $abTestNomenclatureImageChain;

        return $this;
    }

    public function getVisits(): ?int
    {
        return $this->visits;
    }

    public function setVisits(?int $visits): self
    {
        $this->visits = $visits;

        return $this;
    }

    public function getAbTestCartKindItemsIndex(): ?int
    {
        return $this->abTestCartKindItemsIndex;
    }

    public function setAbTestCartKindItemsIndex(?int $abTestCartKindItemsIndex): self
    {
        $this->abTestCartKindItemsIndex = $abTestCartKindItemsIndex;

        return $this;
    }

    /**
     * @return Collection|ErpLog[]
     */
    public function getErpLogs(): Collection
    {
        return $this->erpLogs;
    }

    public function addErpLog(ErpLog $erpLog): self
    {
        if (!$this->erpLogs->contains($erpLog)) {
            $this->erpLogs[] = $erpLog;
            $erpLog->setOrders($this);
        }

        return $this;
    }

    public function removeErpLog(ErpLog $erpLog): self
    {
        if ($this->erpLogs->contains($erpLog)) {
            $this->erpLogs->removeElement($erpLog);
            // set the owning side to null (unless already changed)
            if ($erpLog->getOrders() === $this) {
                $erpLog->setOrders(null);
            }
        }

        return $this;
    }
}

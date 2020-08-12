<?php


namespace App\Service\Export;


use App\Repository\CurrencyRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ExportManager
{
    private const DYNAMIC_UPLOAD = true;
    private string $filePath;
    private string $fileName;
    private string $fullFileName;
    private bool $result;
    private Serializer $serializer;
    private KernelInterface $appKernel;

    public function __construct(KernelInterface $appKernel, CurrencyRepository $currencyRepository)
    {
        $this->appKernel = $appKernel;
        $encoders = array(new CsvEncoder());
        $normalizers = array(new ObjectNormalizer());
        $this->serializer = new Serializer($normalizers, $encoders);
        $this->filePath = $this->appKernel->getProjectDir()."/var/";
    }

    public function export(array $data): ?Response
    {
        if (count($data) > 0) {
            $csvContent = $this->serializer->serialize($data, 'csv');
        } else {
            $csvContent = "";
        }
        return $this->returnFile($csvContent);
    }

    /**
     * @param string $csvContent
     * @return Response|null
     */
    public function returnFile(string $csvContent): ?Response
    {
        if (self::DYNAMIC_UPLOAD) {
            return $this->dynamicFileDownload($csvContent);
        } else {
            return $this->realFileDownload($csvContent);
        }
    }

    /**
     * @param string $fileContent
     * @return Response|null
     */
    public function dynamicFileDownload(string $fileContent): ?Response
    {
        if ($this->createFileName()) {
            $filename = $this->fileName;
            $response = new Response($fileContent);
            $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $filename
            );
            $response->headers->set('Content-Disposition', $disposition);
            return $response;
        }
        return null;
    }

    /**
     * @param string $fileContent
     * @return Response|null
     */
    public function realFileDownload(string $fileContent): ?Response
    {

        if ($this->saveCsvToFile($fileContent)) {
            // TODO if needs
//            $file = new File($this->fullFileName);
//            return $this->file($file);
        }
        return null;
    }
    /**
     * @return bool
     */
    public function getResult(): bool
    {
        return $this->result;
    }

    /**
     * @param $jsonContent
     * @return bool
     */
    private function saveCsvToFile($csvContent): ?bool
    {
        if (false !== file_put_contents($this->createFileName(), $csvContent)) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    private function createFileName(): string
    {
        $this->fileName = 'orders_'.hrtime(true).".csv";
        $this->fullFileName = $this->filePath.$this->fileName;
        return $this->fullFileName;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fullFileName;
    }
}
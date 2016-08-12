<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ConvertPdf2Image
 *
 * @author thienanh
 */
class ConvertPdf2Image {
    //put your code here
    protected $pdfFile;
    protected $resolution = 96;
    protected $outputFormat = '';
    protected $page = 1;
    protected $validOutputFormats = ['jpg', 'jpeg'];
    /**
     * @param string $pdfFile The path to the pdffile.
     *
     * @throws \Spatie\PdfToImage\Exceptions\PdfDoesNotExist
     */
    public function __construct($pdfFile)
    {
        if (!file_exists($pdfFile)) {
            throw new PdfDoesNotExist();
        }
        $this->pdfFile = $pdfFile;
    }
    /**
     * Set the raster resolution.
     *
     * @param int $resolution
     *
     * @return $this
     */
    public function setResolution($resolution)
    {
        $this->resolution = $resolution;
        return $this;
    }
    /**
     * Set the output format.
     *
     * @param string $outputFormat
     *
     * @return $this
     *
     * @throws \Spatie\PdfToImage\Exceptions\InvalidFormat
     */
    public function setOutputFormat($outputFormat)
    {
        if (!$this->isValidOutputFormat($outputFormat)) {
            throw new InvalidFormat('Format '.$outputFormat.' is not supported');
        }
        $this->outputFormat = $outputFormat;
        return $this;
    }
    /**
     * Determine if the given format is a valid output format.
     *
     * @param $outputFormat
     *
     * @return bool
     */
    public function isValidOutputFormat($outputFormat)
    {
        return in_array($outputFormat, $this->validOutputFormats);
    }
    /**
     * Set the page number that should be rendered.
     *
     * @param int $page
     *
     * @return $this
     *
     * @throws \Spatie\PdfToImage\Exceptions\PageDoesNotExist
     */
    public function setPage($page)
    {
        if ($page > $this->getNumberOfPages()) {
            throw new PageDoesNotExist('Page '.$page.' does not exist');
        }
        $this->page = $page;
        return $this;
    }
    /**
     * Get the number of pages in the pdf file.
     *
     * @return int
     */
    public function getNumberOfPages()
    {
        return (new \Imagick($this->pdfFile))->getNumberImages();
    }
    /**
     * Save the image to the given path.
     *
     * @param string $pathToImage
     *
     * @return bool
     */
    public function saveImage($pathToImage)
    {
        $imageData = $this->getImageData($pathToImage);
        file_put_contents($pathToImage, $imageData);
        return true;
    }
    /**
     * Save the file as images to the given directory.
     *
     * @param string $directory
     * @param string $prefix
     *
     * @return array $files the paths to the created images
     */
    public function saveAllPagesAsImages($directory, $prefix = '')
    {
        $numberOfPages = $this->getNumberOfPages();
        if ($numberOfPages === 0) {
            return [];
        }
        return array_map(function ($pageNumber) use ($directory, $prefix) {
            $this->setPage($pageNumber);
            $destination = "{$directory}/{$prefix}{$pageNumber}.{$this->outputFormat}";
            $this->saveImage($destination);
            return $destination;
        }, range(1, $numberOfPa));
        
        
        
    }
    /**
     * Return raw image data.
     *
     * @param string $pathToImage
     *
     * @return \Imagick
     */
    public function getImageData($pathToImage)
    {
        $imagick = new \Imagick();
        $imagick->setResolution($this->resolution, $this->resolution);
        $imagick->readImage(sprintf('%s[%s]', $this->pdfFile, $this->page - 1));
        $imagick->setFormat($this->determineOutputFormat($pathToImage));
        return $imagick;
    }
    /**
     * Determine in which format the image must be rendered.
     *
     * @param $pathToImage
     *
     * @return string
     */
    protected function determineOutputFormat($pathToImage)
    {
        $outputFormat = pathinfo($pathToImage, PATHINFO_EXTENSION);
        if ($this->outputFormat != '') {
            $outputFormat = $this->outputFormat;
        }
        $outputFormat = strtolower($outputFormat);
        if (!$this->isValidOutputFormat($outputFormat)) {
            $outputFormat = 'jpg';
        }
        return $outputFormat;
    }
    
    
}

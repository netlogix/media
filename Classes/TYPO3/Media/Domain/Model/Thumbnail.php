<?php
namespace TYPO3\Media\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Media".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Object\ObjectManagerInterface;
use TYPO3\Media\Domain\Model\Adjustment\ResizeImageAdjustment;
use TYPO3\Media\Domain\Strategy\ThumbnailGeneratorStrategy;
use TYPO3\Media\Exception;

/**
 * A system-generated preview version of an Asset
 *
 * @Flow\Entity
 */
class Thumbnail implements ImageInterface {

	use DimensionsTrait;

	/**
	 * @var ThumbnailGeneratorStrategy
	 * @Flow\Inject
	 */
	protected $generatorStrategy;

	/**
	 * @var Asset
	 * @ORM\ManyToOne(cascade={"persist", "merge"}, inversedBy="thumbnails")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $originalAsset;

	/**
	 * @var integer
	 * @ORM\Column(nullable = true)
	 */
	protected $maximumWidth;

	/**
	 * @var integer
	 * @ORM\Column(nullable = true)
	 */
	protected $maximumHeight;

	/**
	 * @var \TYPO3\Flow\Resource\Resource
	 * @ORM\OneToOne(orphanRemoval = true, cascade={"all"})
	 * @Flow\Validate(type = "NotEmpty")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $resource;

	/**
	 * @var string
	 */
	protected $ratioMode;

	/**
	 * @var boolean
	 * @ORM\Column(nullable = true)
	 */
	protected $allowUpScaling;

	/**
	 * Constructs a new Thumbnail
	 *
	 * @param AssetInterface $originalAsset The original asset this variant is derived from
	 * @param integer $maximumWidth Maximum width of the generated thumbnail
	 * @param integer $maximumHeight Maximum height of the generated thumbnail
	 * @param string $ratioMode Whether the resulting image should be cropped if both edge's sizes are supplied that would hurt the aspect ratio
	 * @param boolean $allowUpScaling Whether the resulting image should be upscaled
	 * @throws \TYPO3\Media\Exception
	 */
	public function __construct(AssetInterface $originalAsset, $maximumWidth = NULL, $maximumHeight = NULL, $ratioMode = ImageInterface::RATIOMODE_INSET, $allowUpScaling = NULL) {
		$this->originalAsset = $originalAsset;
		$this->maximumWidth = $maximumWidth;
		$this->maximumHeight = $maximumHeight;
		$this->ratioMode = $ratioMode;
		$this->allowUpScaling = $allowUpScaling;
	}

	/**
	 * Initializes this thumbnail
	 *
	 * @param integer $initializationCause
	 */
	public function initializeObject($initializationCause) {
		if ($initializationCause === ObjectManagerInterface::INITIALIZATIONCAUSE_CREATED) {
			$this->refresh();
		}
	}

	/**
	 * Returns the Asset this thumbnail is derived from
	 *
	 * @return \TYPO3\Media\Domain\Model\ImageInterface
	 */
	public function getOriginalAsset() {
		return $this->originalAsset;
	}

	/**
	 * Resource of this thumbnail
	 *
	 * @return Resource
	 */
	public function getResource() {
		return $this->resource;
	}

	/**
	 * @param \TYPO3\Flow\Resource\Resource $resource
	 * @return void
	 */
	public function setResource($resource) {
		$this->resource = $resource;
	}

	/**
	 * @return integer
	 */
	public function getMaximumWidth() {
		return $this->maximumWidth;
	}

	/**
	 * @return integer
	 */
	public function getMaximumHeight() {
		return $this->maximumHeight;
	}

	/**
	 * @return string
	 */
	public function getRatioMode() {
		return $this->ratioMode;
	}

	/**
	 * @return boolean
	 */
	public function isUpscalingAllowed() {
		return (boolean)$this->allowUpScaling;
	}

	/**
	 * @param integer $width
	 * @return void
	 */
	public function setWidth($width) {
		$this->width = (integer)$width;
	}

	/**
	 * @param integer $height
	 * @return void
	 */
	public function setHeight($height) {
		$this->height = (integer)$height;
	}

	/**
	 * Refreshes this asset after the Resource has been modified
	 *
	 * @return void
	 */
	public function refresh() {
		$this->generatorStrategy->refresh($this);
	}
}
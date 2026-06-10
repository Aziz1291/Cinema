<?php
class Film
{
	private ?int $id = null;
	private ?string $title = null;
	private ?int $duration = null;
	private ?string $description = null;
	private ?string $created_at = null;
	private ?string $poster_image = null;
	private ?int $active_sessions_count = 0;

	public function __construct($title = null, $duration = null, $description = null, $created_at = null, $poster_image = null)
	{
		if ($title !== null) {
			$this->title = $title;
			$this->duration = $duration;
			$this->description = $description;
			$this->created_at = $created_at;
			$this->poster_image = $poster_image;
		}
	}

	public function getId()
	{
		return $this->id;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getDuration()
	{
		return $this->duration;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getCreatedAt()
	{
		return $this->created_at;
	}

	public function getPosterImage()
	{
		return $this->poster_image;
	}

	public function setTitle($title)
	{
		$this->title = $title;
	}

	public function setDuration($duration)
	{
		$this->duration = $duration;
	}

	public function setDescription($description)
	{
		$this->description = $description;
	}

	public function setCreatedAt($created_at)
	{
		$this->created_at = $created_at;
	}

	public function setPosterImage($poster_image)
	{
		$this->poster_image = $poster_image;
	}

	public function getActiveSessionsCount()
	{
		return $this->active_sessions_count;
	}

	public function setActiveSessionsCount($count)
	{
		$this->active_sessions_count = $count;
	}
}
?>

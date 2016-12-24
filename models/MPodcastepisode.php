<?php

class MPodcastepisode extends BeanModel {
  public function duration_sec() {
    return timetosec($this->duration);
  }

  public function size_mb() {
    return number_format($this->length / 1024 / 1024, 2);
  }
}
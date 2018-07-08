<?php
namespace Services;

require_once __DIR__ . '/../vendor/autoload.php';

class CSSService
{
  public $baseFontSize;
  public $htmlPercentage;
  public $style;

  public function init(float $baseFontSize = 16.0)
  {
    $this->baseFontSize = $baseFontSize;
    $this->htmlPercentage = $baseFontSize * 100.0 / 16.0;
    $this->style = <<<"EOS"

/* {$this->baseFontSize} */

html {
  color: #555;
  font-size: {$this->htmlPercentage}%;
}

button,
div,
h1,h2,h3,h4,h5,
input,
select,
table {
  font-size: 1.0rem;
  letter-spacing: 0.0em;
  line-height: 1.75em;
  margin: 0px;
/*
  padding-bottom: 0.4em;
  */

}

h1 { font-size: 1.6rem; }
h2 { font-size: 1.4rem; }
h3 { font-size: 1.2rem; }
h4 { font-size: 1.1rem; }
h5 { font-size: 1.0rem; }
button, input, select {
  height: 2.4em;
}

button, input[type="button"], input[type="submit"] {
  padding-top: 0.0em;
  padding-bottom: 0.0em;
  padding-left: 0.8em;
  padding-right: 0.8em;
}

input[type="text"] {
  padding: 0.5em;
  border: solid 1px #999;
}

input[type="text"].error {
  border-color: #F66;
}

div.container {
  padding-left: 1.0rem;
  padding-top: 1.0rem;
}

div.belt1 {
  padding-left: 1.0rem;
  padding-top: 1.0rem;
  padding-bottom: 0.8rem;
}

div.belt2 {
  background: #E6E6E6;
  /*
  background: #F6F6F6;
  border-top: solid 1px #CCC;
  border-bottom: solid 1px #CCC;
  */
  padding-left: 1.0rem;
  padding-top: 1.0rem;
  padding-bottom: 0.8rem;
}

div.belt3 {
  border-bottom: solid 1px #CCC;
  padding-left: 1.0rem;
  padding-top: 1.0rem;
  padding-bottom: 0.8rem;
}

div.contents {
  padding-left: 1.0rem;
  padding-top: 1.0rem;
  padding-bottom: 1.0rem;
}

EOS;
    return $this;
  }


}

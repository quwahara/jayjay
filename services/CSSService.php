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
label,
select,
table {
  font-size: 1.0rem;
  letter-spacing: 0.0em;
  line-height: 1.75em;
  margin: 0px;
}

label, .label {
  display: inline-block;
  font-weight: bold;
  width: 10em;
  text-align: right;
  margin-right: 1.0rem;
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

input[type="text"], input[type="password"], select {
  padding: 0.5em;
  border: solid 1px #999;
}

input[type="text"].error, select.error {
  border-color: #F66;
}

button.link {
  display: inline-block;
  position: relative;
  background-color: transparent;
  cursor: pointer;
  border: 0;
  padding: 0;
  color: inherit;
  /* color: black; */
  /* color: #00f; */
  /* text-decoration: underline; */
  font: inherit;
}

.text-right {
  text-align: right;
}

.hide {
  display: none;
}

.ph::before {
  content: "\\0000a0";
}

div.belt {
  padding-left: 1.0rem;
  padding-right: 1.0rem;
  padding-top: 1.0rem;
  padding-bottom: 0.8rem;
}

.belt.info {
  color: hsl(200, 85%, 25%);
  background: hsl(200, 85%, 85%);
  border-top: solid 1px hsl(200, 85%, 25%);
  border-bottom: solid 1px hsl(200, 85%, 25%);
}

.belt.success {
  color: hsl(100, 85%, 25%);
  background: hsl(100, 85%, 85%);
  border-top: solid 1px hsl(100, 85%, 25%);
  border-bottom: solid 1px hsl(100, 85%, 25%);
}

.belt.warning {
  color: hsl(60, 85%, 25%);
  background: hsl(60, 85%, 85%);
  border-top: solid 1px hsl(60, 85%, 25%);
  border-bottom: solid 1px hsl(60, 85%, 25%);
}

.belt.error {
  color: hsl(0, 85%, 25%);
  background: hsl(0, 85%, 85%);
  border-top: solid 1px hsl(0, 85%, 25%);
  border-bottom: solid 1px hsl(0, 85%, 25%);
}

.bg-mono-00 { background: hsl(0, 0%, 0%); } /* 00/12 */
.bg-mono-01 { background: hsl(0, 0%, 8%); } /* 01/12 */
.bg-mono-02 { background: hsl(0, 0%, 17%); } /* 02/12 */
.bg-mono-03 { background: hsl(0, 0%, 25%); } /* 03/12 */
.bg-mono-04 { background: hsl(0, 0%, 33%); } /* 04/12 */
.bg-mono-05 { background: hsl(0, 0%, 42%); } /* 05/12 */
.bg-mono-06 { background: hsl(0, 0%, 50%); } /* 06/12 */
.bg-mono-07 { background: hsl(0, 0%, 58%); } /* 07/12 */
.bg-mono-08 { background: hsl(0, 0%, 67%); } /* 08/12 */
.bg-mono-09 { background: hsl(0, 0%, 75%); } /* 09/12 */
.bg-mono-10 { background: hsl(0, 0%, 83%); } /* 10/12 */
.bg-mono-11 { background: hsl(0, 0%, 92%); } /* 11/12 */
.bg-mono-12 { background: hsl(0, 0%, 100%); } /* 12/12 */

.bl-mono-00 { border-bottom: solid 1px hsl(0, 0%, 0%); } /* 00/12 */
.bl-mono-01 { border-bottom: solid 1px hsl(0, 0%, 8%); } /* 01/12 */
.bl-mono-02 { border-bottom: solid 1px hsl(0, 0%, 17%); } /* 02/12 */
.bl-mono-03 { border-bottom: solid 1px hsl(0, 0%, 25%); } /* 03/12 */
.bl-mono-04 { border-bottom: solid 1px hsl(0, 0%, 33%); } /* 04/12 */
.bl-mono-05 { border-bottom: solid 1px hsl(0, 0%, 42%); } /* 05/12 */
.bl-mono-06 { border-bottom: solid 1px hsl(0, 0%, 50%); } /* 06/12 */
.bl-mono-07 { border-bottom: solid 1px hsl(0, 0%, 58%); } /* 07/12 */
.bl-mono-08 { border-bottom: solid 1px hsl(0, 0%, 67%); } /* 08/12 */
.bl-mono-09 { border-bottom: solid 1px hsl(0, 0%, 75%); } /* 09/12 */
.bl-mono-10 { border-bottom: solid 1px hsl(0, 0%, 83%); } /* 10/12 */
.bl-mono-11 { border-bottom: solid 1px hsl(0, 0%, 92%); } /* 11/12 */
.bl-mono-12 { border-bottom: solid 1px hsl(0, 0%, 100%); } /* 12/12 */

div.contents {
  padding-left: 1.0rem;
  padding-top: 1.0rem;
  padding-bottom: 1.0rem;
}

div.row {
  margin: 0.0rem 1.0rem 1.0rem 0.0rem;
}

.fold {
    padding: 0;
    transition: height 0.6s;
    height: 0px;
    /*
    background-color: red;
    */
}

.fold>* {
    display: none;
}

.un.fold {
    padding: 1.0rem 1.0rem 0.8rem 1.0rem;
    height: 4.0rem;
}

.un.fold>* {
    display: block;
}

EOS;
    return $this;
  }


}

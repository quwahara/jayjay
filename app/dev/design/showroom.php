<?php
function calcSize($fsize) {
  $s = [];
  $s['fsize'] = $fsize;
  $s['hpct'] = $fsize * 100.0 / 16.0;

  foreach ([1, 2, 3, 6] as $v) {
    $s["z{$v}em"] = $fsize * $v / 10.0;
  }

  return $s;
}

$sizeSet = [];

for ($i = 6; $i < 20; $i += 2) {
  $sizeSet["fsize{$i}"] = calcSize($i);
}

$s = $sizeSet['fsize12'];
$style = <<<EOT

/* {$s['fsize']} */
html {
  font-size: {$s['hpct']}%;
}
button,
div,
h1,h2,h3,h4,h5,
input,
select {
  letter-spacing: 0.0em;
/*  line-height: 1.75em; */
  line-height: 1.75em;
  margin: 0px;
  padding-bottom: 0.4em;
}
h1 { font-size: 1.6rem; }
h2 { font-size: 1.4rem; }
h3 { font-size: 1.2rem; }
h4 { font-size: 1.1rem; }
h5 { font-size: 1.0rem; }
input, select {
height: 2.4em;
}
/*
*/
div.container {
  padding-left: 8px;
  padding-top: 8px;
}

EOT;

?><html>

<head>
  <link rel="stylesheet" type="text/css" href="../../../js/lib/node_modules/normalize.css/normalize.css">
  <!-- <link rel="stylesheet" type="text/css" href="../../../css/global.css"> -->
  <?php
  echo '<style>'
  . $style
  . '</style>';
  ?>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="../../../js/lib/node_modules/axios/dist/axios.js"></script>
  <script src="../../../js/trax/trax.js"></script>
  <script src="../../../js/lib/global.js"></script>
</head>

<body>
  <div class="container">
    <div>
      <table>
        <thead>
          <tr>
            <th>F Size</th>
            <th>html pct</th>
            <th>0.1em</th>
            <th>0.3em</th>
            <th>0.6em</th>
          </tr>
        </thead>
        <tbody>
          <?php for ($i = 6; $i < 20; $i += 2):
            $fsize = $i;
            $hpct = $fsize * 100.0 / 16.0;
            $z1em = $fsize * 0.1;
            $z3em = $fsize * 0.3;
            $z6em = $fsize * 0.6;
          ?>
        <tr>
            <td><?= $fsize ?></td>
            <td><?= $hpct ?></td>
            <td><?= $z1em ?></td>
            <td><?= $z3em ?></td>
            <td><?= $z6em ?></td>
          </tr>
          <?php endfor; ?>
        </tbody>
      </table>
    </div>
    <div>
      <h1>This is an H1.</h1>
      <h2>This is an H2.</h2>
      <h3>This is an H3.</h3>
      <h4>This is an H4.</h4>
      <h5>This is an H5.</h5>
    </div>
    <form>
      
      <div>
        <label for="someText1">Some text 1</label>
        <input type="text" name="someText1" size="20" value="1____+____2____+____">
      </div>

      <div>
        <label for="someText2">Some text 2</label>
        <input type="text" name="someText2" size="20" value="1____+____2____+____">
      </div>

      <div>
        <label for="someText3">Some text 3</label>
        <input type="text" name="someText3" size="20" value="1____+____2____+____">
      </div>

      <div>
        <label for="someText4">Some text 4</label>
        <input type="text" name="someText4" size="20" value="1____+____2____+____">
      </div>

      <div>
        <label for="someText5">Some text 5</label>
        <input type="text" name="someText5" size="20" value="1____+____2____+____">
      </div>

      <div>
        <label for="someSelect1">Some select 1</label>
        <select name="someSelect1">
          <option>Option 1</option>
          <option>Option 2</option>
          <option>Option 3</option>
        </select>
      </div>

      <div>
        <button type="button">Save</button>
        <button type="button">OK</button>
        <button type="button">Cancel</button>
      </div>

      <div>
<?= nl2br(htmlspecialchars($style)) ?>
      </div>

    </form>
  </div>
</body>

</html>

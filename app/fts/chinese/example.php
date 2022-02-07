<html>
  <head>
    <meta http-equiv="Content-Type" content="text/xhtml; charset=utf-8" />
    <title>中文全文检索样例 @ PostgreSQL</title>
    <style>
     * {
         font-family: "Noto Sans", "Noto Sans SC", "Noto Sans TC", "Padauk", "ATaiThamKHNewV3-Normal", Arial, Verdana;
     }
     td {
         border-right-style: solid;
         border-top-style: solid;
     }
     table {
         border-style: solid;
     }
     table span {
         background-color: yellow;
         font-size: 1.2em;
     }
     th {
         font-weight: bold;
     }
     input[name="q"] {
         width: 70%;
     }
    </style>
  </head>

  <body>
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      // collect value of input field
      $q = $_POST['q'];
    }
    ?>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
      Text: <input type="text" name="q" value="<?php echo $q ?>">
      <input type="submit" value="Query">
    </form>
    <?php
    require_once '../../config.php';

    if (empty($q)) {
      echo "Query is empty";
    } else {
      // Connecting, selecting database
      $dbconn = pg_connect("host="._DB_HOST_." dbname="._DB_NAME_." user="._DB_USERNAME_." password="._DB_PASSWORD_)
      or die('Could not connect: ' . pg_last_error());

      // Performing SQL query
      $query = "SELECT
        ts_rank('{0.1, 0.2, 0.4, 1}',
        full_text_search_weighted,
        websearch_to_tsquery('jiebacfg', '$q')) as rank,
        ts_headline('jiebacfg', author,
                              websearch_to_tsquery('jiebacfg', '$q'),
                              'StartSel = <span>, StopSel = </span>')
                 AS highlight_author,
        ts_headline('jiebacfg', title,
                              websearch_to_tsquery('jiebacfg', '$q'),
                              'StartSel = <span>, StopSel = </span>')
                 AS highlight_title,
        ts_headline('jiebacfg', subtitle,
                              websearch_to_tsquery('jiebacfg', '$q'),
                              'StartSel = <span>, StopSel = </span>')
                 AS highlight_subtitle,
        ts_headline('jiebacfg', content,
                              websearch_to_tsquery('jiebacfg', '$q'),
                              'StartSel = <span>, StopSel = </span>')
                 AS highlight_content,
        author, title, subtitle, content, full_text_search_weighted
        FROM sample
        WHERE full_text_search_weighted @@ websearch_to_tsquery('jiebacfg', '$q')
        ORDER BY rank DESC LIMIT 20;";
      $result = pg_query($query) or die('Query failed: ' . pg_last_error());

      // Printing results in HTML
      echo "<table>\n";
      echo "<tr>
                 <th>rank</th>
                 <th>author (hl)</th>
                 <th>title (hl)</th>
                 <th>subtitle (hl)</th>
                 <th>content (hl)</th>
                 <th>author</th>
                 <th>title</th>
                 <th>subtitle</th>
                 <th>content</th>
                 <th>TSVECTOR</th>
              </tr>";
      while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
        echo "\t<tr>\n";
        foreach ($line as $col_value) {
          echo "\t\t<td><div class='cell'>$col_value</div></td>\n";
        }
        echo "\t</tr>\n";
      }
      echo "</table>\n";

      // Free resultset
      pg_free_result($result);

      // Closing connection
      pg_close($dbconn);
    }
    ?>
  </body>
</html>

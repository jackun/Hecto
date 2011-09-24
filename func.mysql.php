<?php
class MySqlC
   {
      var $con;
      function MySqlC($host, $username, $password) {
            $this->con = mysql_connect($host, $username, $password)
            or die('Could not connect: ' . mysql_error());
         }

      function select($db){
            mysql_select_db($db) or die('Could not select database ('.$db.')');
         }

      function q($q){
            $result = mysql_query($q) or die('Query failed: ' . mysql_error());
            return $result;
         }

      function kuva_tabel($tabel, $select = "*", $where = "1", $limit = "100"){
        $q = "SELECT $select FROM $tabel WHERE $where ";
        $q.= "LIMIT $limit";
        $result = mysql_query($q);
        echo "<table border=1><tr>";

        $i = 0;
         while ($i < mysql_num_fields($result)) {
            $meta = mysql_fetch_field($result, $i);
            if ($meta)
                echo "<td>".$meta->name."(".$meta->max_length.")</td>";
            $i++;
         }
        echo "</tr>";
        while($rida = mysql_fetch_assoc($result))
        {
            echo "<tr>";
            foreach($rida as $elem) echo "<td>$elem</td>";
            echo "</tr>";
        }
        echo "</table>";

      }
      function row_id($tabel, $nr, $veerg = "id")
         {
            $id = (int)$nr;
            $result = mysql_query("SELECT * FROM $tabel WHERE $veerg = $id LIMIT 1");
            return mysql_fetch_assoc($result);
         }

      
      function close()
       {
            mysql_close($this->con)
            or die("Ei saanud sulgeda (".mysql_error().")");
       }
   }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
        <style>
            table,tr,td,th{
                border:1px solid black;
                border-collapse:collapse
            }
        </style>
    </head>
    <?php
    $path = getcwd();
    walkDir($path);

    $out = "<table>";
    $out .= theader($numfiles);
    $out .= tdata("Number of files", $numfiles);
    $out .= tdata("Lines of code", $numlines);
    $out .= tdata("Size in KBytes", $numsize);
    $out .= "</table>";

    echo "<h2>Path ;  $path</h2><p>";
    echo $out;
    exit;
    ?>

    <body>
        <?php
        $numfiles = [];
        $numlines = [];
        $numsize = [];

        function readSourceFile($file, $ext) {
            global $numfiles, $numlines, $numsize;
            $numfiles[$ext]++;
            $numlines[$ext] += count(file($file));
            $numsize[$ext] += floor(max(1, filesize($file) / 1024));
        }

        function walkDir($path) {
            if (is_dir($path)) {
                $dh = opendir($path);
                while (($file = readdir($dh)) !== false) {
                    if ($file == '.' || $file == '..' || substr($file, 0, 1) == '.') {
                        continue;
                    }
                    if (is_dir($path . '/' . $file)) {
                        walkDir($path . '/' . $file);
                    }
                    $arr = explode('.', $file);
                    if (count($arr) == 1) {
                        continue;
                    }
                    $ext = $arr[count($arr) - 1];
                    if (stripos('  php js css html ', " $ext ") > 0) {
                        readSourceFile($path . '/' . $file, $ext);
                    }
                }
                closedir($dh);
                return;
            }
        }

        function theader($arr) {
            $out = [];
            $out[] = "<tr><th></th>";
            foreach ($arr as $k => $v) {
                $out[] = "<th>$k</th>";
            }
            $out[] = "</tr>";
            return implode('', $out);
        }

        function tdata($what, $arr) {
            $out = [];
            $out[] = "<tr><th>$what</th>";
            foreach ($arr as $k => $v) {
                $out[] = "<td>$v</td>";
            }
            $out[] = "</tr>";
            return implode('', $out);
        }

        function out($arr) {

            foreach ($arr as $k => $v) {
                echo "$k => $v<br>";
            }
        }
        ?>
    </body>
</html>

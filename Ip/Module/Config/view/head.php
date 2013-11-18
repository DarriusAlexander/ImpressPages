<?php if ($this->getDoctype() == \Ip\View::DOCTYPE_HTML5) { ?>
    <meta charset="<?php echo $charset; ?>" />
<?php } else { ?>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset; ?>" />
<?php } ?>
    <title><?php echo htmlspecialchars($title); ?></title>
    <link rel="shortcut icon" href="<?php echo htmlspecialchars($favicon); ?>" />
    <meta name="keywords" content="<?php echo htmlspecialchars($keywords); ?>" />
    <meta name="description" content="<?php echo htmlspecialchars($description); ?>" />
    <meta name="generator" content="ImpressPages CMS" />
<?php foreach ($css as $key => $file) { ?>
    <link href="<?php echo $file['value']; ?>" rel="stylesheet" type="text/css" <?php
    if (is_array($file['attributes'])) {
        echo join(
            ' ',
            array_map(
                function ($sKey, $sValue) {
                    return ipEsc($sKey) . '="' . ipEsc($sValue) . '"';
                },
                array_keys($file['attributes']),
                array_values($file['attributes'])
            )
        );
    }
    ?>/>
<?php } ?>

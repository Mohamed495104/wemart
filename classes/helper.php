<?php
function getImageSrc($imagePath, $isInSubfolder = false) {
    if (empty($imagePath)) {
        return ($isInSubfolder ? '../' : '') . 'assets/images/no-image.png';
    }
    
    // If it's already an absolute URL or starts with /, return as is
    if (preg_match('/^(https?:\/\/|\/)/i', $imagePath)) {
        return $imagePath;
    }
    
    return ($isInSubfolder ? '../' : '') . $imagePath;
}
?>
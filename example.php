<?php

    /* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

    /**
     * Example on how to use the Seeker Class
     *
     * LICENSE:
     * This program is free software; you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation; either version 2 of the License, or
     * (at your option) any later version.
     *
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
     * GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License along
     * with this program; if not, write to the Free Software Foundation, Inc.,
     * 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
     * http://www.gnu.org/copyleft/gpl.html
     *
     * @package     Seeker
     * @author      Nicholas Dunnaway
     * @copyright   2007 php|uber.leet
     * @license     http://www.gnu.org/copyleft/gpl.html
     * @link        http://uber.leetphp.com
     * @version     CVS: $Id: example.php,v 1.3 2007/04/02 17:02:25 ndunnaway Exp $
     * @since       File available since Release 1.01
     *
     */

    /**
     * Seeker Class File.
     *
     */
    require_once 'libs\Seeker.class.php';

    echo '----- Single Vin, Single Location -----' . "\n";

    $Seeker = new Seeker();
    $Seeker->mNeedle = '1GCEC0000000E6772';
    $Seeker->mHayStack = 'searchDir';
    $Seeker->sResultFile = 'Result.txt';
    $Seeker->Search();

    echo "\n" . '----- Multi Vin, Single Location -----' . "\n";

    $SearchFor[] = '1HGCM000000005818';
    $SearchFor[] = 'WDBKK000000088377';
    $SearchFor[] = '1N4AL000000069647';

    $Seeker = new Seeker();
    $Seeker->mNeedle = $SearchFor;
    $Seeker->mHayStack = 'searchDir';
    $Seeker->sResultFile = 'Result.txt';
    $Seeker->Search();

    unset($SearchFor); // Clean up

    echo "\n" . '----- Single Vin, Multi Location -----' . "\n";

    $SearchDir[] = 'searchDir';
    $SearchDir[] = 'searchDir2';

    $Seeker = new Seeker();
    $Seeker->mNeedle = '1GCEC000000036772';
    $Seeker->mHayStack = $SearchDir;
    $Seeker->sResultFile = 'Result.txt';
    $Seeker->Search();

    unset($SearchDir); // Clean up

    echo "\n" . '----- Multi Vin, Multi Location -----' . "\n";

    $SearchFor[] = '1HGCM600000005818';
    $SearchFor[] = 'WDBKK400000008377';
    $SearchFor[] = '1N4AL100000009647';

	$SearchDir[] = 'searchDir';
    $SearchDir[] = 'searchDir2';

    $Seeker = new Seeker();
    $Seeker->mNeedle = $SearchFor;
    $Seeker->mHayStack = $SearchDir;
    $Seeker->sResultFile = 'Result.txt';
    $Seeker->Search();

    unset($SearchFor); // Clean up
    unset($SearchDir); // Clean up

    echo "\n" . '----- FileList Vin, Single Location -----' . "\n";

    $Seeker = new Seeker();
    $Seeker->mNeedle = 'vin.txt';
    $Seeker->mHayStack = 'searchDir';
    $Seeker->sResultFile = 'Result.txt';
    $Seeker->Search();

    echo "\n" . '----- FileList Vin, Multi Location -----' . "\n";

    $SearchDir[] = 'searchDir';
    $SearchDir[] = 'searchDir2';

    $Seeker = new Seeker();
    $Seeker->mNeedle = 'vin.txt';
    $Seeker->mHayStack = $SearchDir;
    $Seeker->sResultFile = 'Result.txt';
    $Seeker->Search();

    unset($SearchDir); // Clean up

    echo "\n" . 'Script Done' . "\n";
?>

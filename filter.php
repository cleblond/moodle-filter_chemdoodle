<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * easyomechjs question type version information.
 *
 * @package    filter
 * @subpackage chemdoodle
 * @copyright  2014 onwards Carl LeBlond
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class filter_chemdoodle extends moodle_text_filter {

    public function setup($page, $context) {
        global $CFG;
        
        // This only requires execution once per request.
        static $cdinitialised = false;

        if (empty($cdinitialised)) {

/*
	    $url= '/filter/chemdoodle/module.js';
            $url = new moodle_url($url);
            $moduleconfig = array(
                'name' => 'jsmol',
                'fullpath' => $url
            );
           $page->requires->js_module($moduleconfig);
*/

            $u = $CFG->wwwroot;
            $newtext = '';
            $newtext = '
            <link rel="stylesheet" href="/chemdoodle/ChemDoodleWeb.css\" type="text/css" />
            <script src="' . $u . '/filter/chemdoodle/cwc/ChemDoodleWeb-libs.js" type="text/javascript"></script>
            <script src="' . $u . '/filter/chemdoodle/cwc/ChemDoodleWeb.js" type="text/javascript"></script>
            <script src="' . $u . '/filter/chemdoodle/cwc/sketcher/ChemDoodleWeb-sketcher.js" type="text/javascript"></script>
            <script src="' . $u . '/filter/chemdoodle/module.js" type="text/javascript"></script>
';

        $cdinitialised = true;
        }
        echo $newtext;
    }

    public function filter($text, array $options = array()) {
        global $CFG;
        $u = $CFG->wwwroot;
        if (preg_match('|https?://.*?/|', $u)) {
            $relurl = preg_replace('|https?://.*?/|', '', $u);
        } else {
            $relurl = ''; // This will typically be the case if Moodle is the web root.
        }
        $numdirs = substr_count($_SERVER['PHP_SELF'], '/') - 1;
        if ($numdirs == 0) {
            $relurl = './' . $relurl;
        } else {
            $relurl = str_repeat('../', $numdirs) . $relurl;
        }
        $host = preg_replace('~^.*://([^:/]*).*$~', '$1', $u);
        $search = '/<a\\b([^>]*?)href=\"((?:\.|\\\|https?:\/\/' . $host
            . ')[^\"]+\.(jdx))\??(.*?)\"([^>]*)>(.*?)<\/a>(\s*JMOLSCRIPT\{(.*?)\})?/is';
        $infodual = get_string('infodual', 'filter_chemdoodle');
        $infoseeker = get_string('infoseeker', 'filter_chemdoodle');
        $info = get_string('infoperspective', 'filter_chemdoodle');
        $callbackfunction = '
        $a = uniqid();
        if(preg_match(\'/title=(\w{1,20})/\', $matches[4], $optmatch)){
            if($optmatch[1]=="none"){
            $title = "spectrum$a.title = \'\';";
            }else{
            $title = "spectrum$a.title = \'$optmatch[1]\';";}
             }else{
               $title = "";
        }
        if(preg_match(\'/minx=(\d{1,4})/\', $matches[4], $optmatch)){
            $minx = "spectrum$a.minX = \'$optmatch[1]\';";
             }else{
                       $minx = "";
        }
        if(preg_match(\'/flipxaxis=(\w{1,5})/\', $matches[4], $optmatch)){
            if($optmatch[1] == "false"){
            $flip = "component$a.specs.plots_flipXAxis = false ;";
            }else{
            $flip = "component$a.specs.plots_flipXAxis = true ;";
            }
             }else{
                       $flip = "component$a.specs.plots_flipXAxis = true ;";
        }
        if(preg_match(\'/maxx=(\d{1,4})/\', $matches[4], $optmatch)){
            $maxx = "spectrum$a.maxX = \'$optmatch[1]\';";
        }else{
            $maxx = "";
        }
        if(preg_match(\'/int=(\w{1,3})/\', $matches[4], $optmatch)){
            if($optmatch[1] == "off"){
            $integ="";
            }
            else{
                        $integ = "component$a.specs.plots_showIntegration =
                        \'true\';component$a.specs.plots_integrationColor = \'#800000\';";
            }
        }
        else{
        $integ = "component$a.specs.plots_showIntegration = \'true\';component$a.specs.plots_integrationColor = \'#800000\';";
        }
             if(!preg_match(\'/c=(\d{1,2})/\', $matches[4], $optmatch))
             {
               $optmatch = array(1=>1);
             }
        if($optmatch[1] == "2"){
            $canvastype="SeekerCanvas";
            $jscall="
            var spectrum$a = ChemDoodle.readJCAMP(jdxstring);
            var component$a = new ChemDoodle.SeekerCanvas(\'component$a\', 500, 200, ChemDoodle.SeekerCanvas.SEEK_PLOT);
            component$a.specs.plots_color=\'#00B918\';
            component$a.specs.plots_integrationColor = \'#800000\';
            component$a.specs.backgroundColor = \'#FFFBC9\';
            component$a.specs.text_font_size = 14.0;
            $flip
            $minx
            $maxx
            $title
            component$a.specs.text_font_families = \'SansSerif\';
            component$a.loadSpectrum(spectrum$a);
";
        $usrinfo="' . $infoseeker . '";
        }
        elseif ($optmatch[1]=="3"){
            $jscall="
            var spectrum$a = ChemDoodle.readJCAMP(jdxstring);
            var component$a = new ChemDoodle.PerspectiveCanvas(\'component$a\', 500, 200);
            var seekerPlot$a = new ChemDoodle.SeekerCanvas(\'seekerPlot$a\', 500, 200, ChemDoodle.SeekerCanvas.SEEK_PLOT);
            component$a.specs.plots_color=\'#00B918\';
            seekerPlot$a.specs.plots_color=\'#00B918\';
            component$a.specs.plots_integrationColor = \'#800000\';
            seekerPlot$a.specs.plots_flipXAxis = true;
            component$a.specs.plots_flipXAxis = true;
            component$a.specs.backgroundColor = \'#FFFBC9\';
            seekerPlot$a.specs.backgroundColor = \'#c7ffc7\';
            $minx
            $maxx
            $title
            $integ
            component$a.loadSpectrum(spectrum$a);
            seekerPlot$a.loadSpectrum(spectrum$a);";
            $canvastype="PerspectiveCanvas";
            $usrinfo="' . $infodual . '";
        }else{
            $jscall="var spectrum$a = ChemDoodle.readJCAMP(jdxstring);
            var component$a = new ChemDoodle.PerspectiveCanvas(\'component$a\', 500, 200);
            component$a.specs.plots_color=\'#00B918\';
            component$a.specs.plots_integrationColor = \'#800000\';
            $flip
            $minx
            $maxx
            $integ
            $title
            component$a.loadSpectrum(spectrum$a);";
            $canvastype="PerspectiveCanvas";
            $usrinfo="' . $info . '";
        }


    return "
    $usrinfo
    <script type=\"text/javascript\">
    var jdxstring=file_get_contents(\'$matches[2]\');
    $jscall
    </script>
";';
        $newtext          = preg_replace_callback($search, create_function('$matches', $callbackfunction), $text);
       
        return $newtext;
    }
}

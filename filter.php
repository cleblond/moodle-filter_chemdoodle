<?php // $id$
////////////////////////////////////////////////////////////////////////
//  jmol plugin filtering for viewing molecules online
// 
//  This filter will replace any links to a .MOL, .CSMOL, .PDB, 
//  .PDB.GZ .XYZ, .CML, .MOL2, .CIF file 
//  with the Javascript needed to display the molecular structure inline
//
//  To activate this filter, go to admin and enable 'jmol'.
//
//  Jmol is designed only to display files held on your own server -
//  remember that!
//
//  Filter written by Dan Stowell and updated by Geoffrey Rowland.
//
////////////////////////////////////////////////////////////////////////

/// This is the filtering function itself.  It accepts the 
/// courseid and the text to be filtered (in HTML form).

//New class and function code for Moodle 2.0 http://docs.moodle.org/en/Development:Filters_2.
class filter_chemdoodle extends moodle_text_filter {

//function jmol_filter($courseid, $text) {
//public function filter($text) {
function filter($text, array $options = array()){
    global $CFG, $jmol1_applet_has_been_initialised;
    
    // The global variable "$filter_jmol_has_initialized"
    //  is used by BOTH the Jmol filter and the Jmol resource type
    //  to ensure that the Jmol applet code is only ever once 
    //  written to any given web page.


    // Jmol requires that we convert our full URL to a relative URL.
    // Otherwise it displays a warning message and refuses to run!
    
    $u = $CFG->wwwroot;
    
    if(preg_match('|https?://.*?/|', $u)){
      $relurl = preg_replace('|https?://.*?/|', '', $u);
    }else{
      $relurl = ''; // This will typically be the case if Moodle is the web root
    }
    $numdirs = substr_count($_SERVER['PHP_SELF'], '/') - 1;
    if($numdirs==0){
      $relurl = './' . $relurl;
    }else{
      $relurl = str_repeat('../', $numdirs) . $relurl;
    }



    $host = preg_replace('~^.*://([^:/]*).*$~', '$1', $u);
    $search = '/<a\\b([^>]*?)href=\"((?:\.|\\\|https?:\/\/' . $host . ')[^\"]+\.(jdx))\??(.*?)\"([^>]*)>(.*?)<\/a>(\s*JMOLSCRIPT\{(.*?)\})?/is';



 $callbackfunction = '
		$a = uniqid();
             if(preg_match(\'/title=(\w{1,20})/\', $matches[4], $optmatch)){
			if($optmatch[1]=="none")                  
			$title = "spectrum$a.title = \'\';";
			else
			$title = "spectrum$a.title = \'$optmatch[1]\';"; 
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
                        $integ = "component$a.specs.plots_showIntegration = \'true\';component$a.specs.plots_integrationColor = \'#800000\';";

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
//  component$a.specs.plots_flipXAxis = true;
  component$a.specs.backgroundColor = \'#FFFBC9\';
  component$a.specs.text_font_size = 14.0;
  $flip
  $minx
  $maxx
  $title
  component$a.specs.text_font_families = \'SansSerif\';
  component$a.loadSpectrum(spectrum$a);



";
	     $usrinfo="<p><b>Note: Place mouse pointer over spectra to read value!</b></p>";
	     }
	     elseif($optmatch[1]=="3"){
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
  seekerPlot$a.loadSpectrum(spectrum$a);

	    ";
	     $canvastype="PerspectiveCanvas";
	     $usrinfo="<p><b>In the Yellow spectra window <u>Zoom In:</u> click+drag  <u>Zoom Out:</u> double-click <u>Slide:</u> shift+drag <u>Scale:</u> scroll (mousewheel)</p><p>Then in the Green spectra place mouse pointer over spectra to read value!</b></p>";
	     }
	     else{
	     $jscall="var spectrum$a = ChemDoodle.readJCAMP(jdxstring);
  var component$a = new ChemDoodle.PerspectiveCanvas(\'component$a\', 500, 200);
  component$a.specs.plots_color=\'#00B918\';
  component$a.specs.plots_integrationColor = \'#800000\';
//  component$a.specs.plots_flipXAxis = true;
//  component$a.specs.backgroundColor = \'#FFFBC9\';
  $flip
  $minx
  $maxx
  $integ
  $title
 component$a.loadSpectrum(spectrum$a);";

	     $canvastype="PerspectiveCanvas";
	     $usrinfo="<p><b>Zoom In:</b> click+drag  <b>Zoom Out:</b> double-click <b>Slide:</b> shift+drag <b>Scale:</b> scroll (mousewheel)</p>";
		}

	    



return "
$usrinfo
<script type=\"text/javascript\">

var jdxstring=file_get_contents(\'$matches[2]\');


  $jscall

</script>


";





';


$newtext = preg_replace_callback($search, create_function('$matches', $callbackfunction), $text);



 
 
  if(($newtext != $text) && !isset($jmol1_applet_has_been_initialised)){
      $jmol1_applet_has_been_initialised = true;
             
             
$newtext = '

<link rel="stylesheet" href="/chemdoodle/ChemDoodleWeb.css\" type="text/css" />
<script src="/chemdoodle/ChemDoodleWeb-libs.js" type="text/javascript"></script>
<script src="/chemdoodle/ChemDoodleWeb.js" type="text/javascript"></script>
<script src="/chemdoodle/sketcher/ChemDoodleWeb-sketcher.js" type="text/javascript"></script>
<script src="'.$u.'/filter/chemdoodle/module.js" type="text/javascript"></script>




'.$newtext;







             
// GR Hack to use popup window for help.php in Moodle 2.0
//
$newtext = '
<script language="javascript" type="text/javascript">

    function MyCallBack(x, y) {
//        document.getElementById("JSVApplet").removeAllHighlights();
        document.getElementById("JSVApplet").addHighlight(x-0.1, x+0.1,241,111,171,200);
        alert("x: "+x+" y: "+y);
    }

    function RevPlot() {
        document.getElementById("JSVApplet").reversePlot();
    }

    function GridToggle() {
        document.getElementById("JSVApplet").toggleGrid();
    }

		function RemoveHighlights() {
				document.getElementById("JSVApplet").removeAllHighlights();
		}
//////




</script>
'.$newtext; 
  } 
return $newtext;
}
}
?>

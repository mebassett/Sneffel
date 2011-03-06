<?php
class LatexRender 
{
	const LATEX_PATH = "/usr/bin/latex";
	const DVIPS_PATH = "/usr/bin/dvips";
	const CONVERT_PATH = 'PATH=/usr/local/bin:$PATH;export PATH;LD_LIBRARY_PATH=/usr/local/lib;export LD_LIBRARY_PATH;/usr/local/bin/convert';

	const TMP_DIR = "/var/www/html/sneffel.com/tmp";
	const CACHE_DIR = "/var/www/html/sneffel.com/latex/cache";

	const URL_PATH = "/latex/cache";

	public $color;
	public $size;

  	function __construct($color,$size) 
  	{
  		$red = substr($color,0,2);  		
  		$green = substr($color,2,2);
  		$blue = substr($color,4,2);
  		$this->color = array(hexdec($red),hexdec($green),hexdec($blue));
  		$this->size=$size;
  	}
	public function render ($text) 
	{
		
			$hash = uniqid();
			$full_name =self::CACHE_DIR . "/" .  $hash . ".png";
			$url = self::URL_PATH . "/" . $hash . ".png";

			if (!is_file($full_name)) 
			{
				$this->render_latex($text, $hash);
				$this->cleanup($hash);
			}

			//$text = substr_replace($text, "<img src=\"$url\" alt=\"Formula: $i\" />", $position, strlen($matches[0][$i]));
		

		return $url;
	}


	private function wrap($thunk) 
	{
		return "\documentclass[12pt]{article}

% add additional packages here" .
"
\usepackage{amsmath}
\usepackage{amsfonts}
\usepackage{amssymb}
\usepackage{pst-plot}
\usepackage{color}
\pagestyle{empty}
\definecolor{mycolor}{RGB}{".$this->color[0].",".$this->color[1].",".$this->color[2]."}
\\renewcommand{\\familydefault}{\sfdefault}


\begin{document}



 \\textcolor{mycolor}{\Large ".stripslashes($thunk)."}


\end{document}";
	}



	private function render_latex($thunk, $hash) 
	{
	
		$thunk = self::wrap($thunk);

		$current_dir = getcwd();
		chdir(self::TMP_DIR);

		// create temporary LaTeX file
		$fp = fopen(self::TMP_DIR . "/$hash.tex", "w+");
		fputs($fp, $thunk);
		fclose($fp);

	
		// run LaTeX to create temporary DVI file
		$command = self::LATEX_PATH .  " --interaction=nonstopmode " .  $hash . ".tex";
		exec($command);

		  // run dvips to create temporary PS file
		$command = self::DVIPS_PATH . " -E $hash" .  ".dvi -o " . "$hash.ps";
		exec($command);	

		// run PS file through ImageMagick to
		// create PNG file
		$command = self::CONVERT_PATH . " +adjoin +antialias  -density 120 $hash.ps   -transparent white $hash.png";	
		exec($command);
		
		$scale = 100*round(1 + ($this->size-1)*(4/14));
		$command = self::CONVERT_PATH . " -scale ".$scale."% $hash.png $hash.png";
		exec($command);


	

		  // copy the file to the cache directory
		copy(self::TMP_DIR."/$hash.png", self::CACHE_DIR ."/$hash.png");

		chdir($current_dir);

	}
	private function cleanup($hash) 
	{
		$current_dir = getcwd();
		chdir(self::TMP_DIR);

		unlink(self::TMP_DIR . "/$hash.tex");
		unlink(self::TMP_DIR . "/$hash.aux");
		unlink(self::TMP_DIR . "/$hash.log");
		unlink(self::TMP_DIR . "/$hash.dvi");
		unlink(self::TMP_DIR . "/$hash.ps");
		unlink(self::TMP_DIR . "/$hash.png");

		chdir($current_dir);
	}
}
?>

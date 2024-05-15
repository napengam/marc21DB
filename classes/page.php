<?php
/*
 * ***********************************************
 * class creating 'bulma' styled elements
 * **********************************************
 */

class page {

    public $out = [];

    function __construct() {
        $this->out = [];
    }

    function docTypeEtal($titel = 'Titel', $style = '') {
        $this->out[] = "<!DOCTYPE html>
         <html lang='en'>
        <head>
         <title>$titel</title>";
        $this->out[] = "<meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0 minimum-scale=1'>";
        $this->out[] = "
        <link rel='stylesheet' href='/bulma/css/bulma.min.css'>
        <link rel='stylesheet' href='/font-awesome-6/css/all.min.css'>  
        <style>";
        $this->out[] = $style;
        $this->out[] = "</style></head><body>";
        $this->renderOut();
    }

    function header($title = '') {
        if ($title == '') {
            $title = "Modules, Scripts, Demos etc";
        }
        $outx = "
        <div id = 'pghead' class = 'container has-background-info-light' style = 'position:sticky;top:0px;z-index: 51'>
            <div class = 'block'>
                <div class = 'hero is-small' >
                    <div class = 'hero-body' style = 'background-image:url(imgHome/sea-boat.jpg);background-repeat:round'>
                        <p style = 'color:white' class = 'title'>
                        <a title='Go to my website' style = 'color:white' href = 'https://hgsweb.de'>
                        <i style = 'font-size:smaller' class = 'fa fa-home'></i> 
                          $title
                              </a> 
                              &nbsp;<span style = 'font-size:smaller'>
                              <a title='find sources at github'  href='https://github.com/napengam/marc21DB'><i class='fab fa-github'></i></a>
                              </span>
                        
                          <p style = 'color:white' class = 'subtitle'>
                          <span id='dnbfile' ></span>
                    </div>
                </div>
                <nav class='level' style='margin-bottom:8px'>
                <div class='level-left'>                   
                    <div id='filter' class='level-item'>
                    </div>
                </div>
                </nav>
                
                <nav id='searchnav' class='level'>                            
                <div class='level-right'>
                    <div class='level-item'>
                        <p> Suche in Titel
                    </div>
                     <div class='level-item'>
                     <input id='title'  type=text size=20 maxlength=40 placeholder='muster*'>
                    </div>
                </div>
                <div class='level-right'>
                    <div class='level-item'>
                        <p> Autor
                    </div>
                     <div class='level-item'>
                     <input id='autor'  type=text size=20 maxlength=40 placeholder='muster*'>
                    </div>
                </div>
                <div class='level-right'>
                    <div class='level-item'>
                        <p> Verlag
                    </div>
                     <div class='level-item'>
                     <input id='verlag'  type=text size=20 maxlength=40 placeholder='muster*'>
                    </div>
                </div>
                 <div class='level-right'>
                    <div class='level-item'>
                        <p> Wähle Datei
                    </div>
                     <div id='selectfile' class='level-item'>
                     
                    </div>
                </div>
                </nav>
            </div>
        </div>";
        $this->out[] = $outx;
        $this->renderOut();
    }

    function navBar() {
        ?>
        <nav class="navbar is-transparent is-info" >
            <div class="navbar-brand">                
                <div class="navbar-burger js-burger" data-target="navbarExampleTransparentExample">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>

            <div id="navbarExampleTransparentExample" class="navbar-menu">
                <div class="navbar-start">
                    <a class="navbar-item" href="#"><b>DNB-Files</b> </a>
                </div>

                <div class="navbar-end">
                    <div class="navbar-item">
                        <div class="field is-grouped">
                            <p class="control">
                                Logout <i class="fa-solid fa-right-from-bracket"></i>
                            </p>
                            <p class="control">

                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </nav>
        <hr>
        <?php
    }

    function footer() {
        $t1 = "title ='Zur ersten Seite' ";
        $t2 = "title ='Zur vorigen Seite'";
        $t3 = "title ='Zur nächsten Seite'";
        $t4 = "title ='Zur letzten Seite'";
        $outx = " 
            <div id = 'pgfoot' class = 'container ' style='background-color:white'>                 
                <footer style = 'padding:10px;margin-top:10px' class = 'footer'>
                    <div class = 'content has-text-centered'>
                    <nav  id='pager' class='pagination is-centered' role='navigation' aria-label='pagination'>  
                        <span id='prev' class='findme pagination-previous is-hidden' >
                        <i id='pfirst' $t1 class=' fa-solid fa-backward-step is-clickable' data-funame='marc21DB.otherPage'></i>&nbsp;&nbsp;&nbsp;&nbsp;
                        <a id='pprev' $t2   data-funame='marc21DB.otherPage' >Previous</a>
                        </span>
                        <span id='next'  class='findme pagination-next is-hidden' >
                       <a id='pnext'  $t3   data-funame='marc21DB.otherPage'>Next page</a>&nbsp;&nbsp;&nbsp;&nbsp;  
                       <i id='plast'  $t4 class='fa-solid fa-forward-step is-clickable'  data-funame='marc21DB.otherPage'></i>
                       </span>
                    <ul class='pagination-list'>
                    <li><img src = 'imgHome/boat.jpg' alt = 'alt' width = '100'> <br>
                    This site uses CSS framework <a href = 'https://bulma.io/' target = 'bulma'> BULMA <i class = 'fas fa fa-external-link'></i></a>
                    </ul>
                    </nav> 
                    </div>
                </footer>
                 
            </div>";
        $this->out[] = $outx;
        $this->renderOut();
    }

    function blockTitelSub($title, $subt) {

        $outx = "
<div class = 'block'>
<p class = 'title is-4'>$title</p>";
        if ($subt) {
            $outx .= "<div class = 'subtitle'>$subt</div>";
        }
        echo $outx;
    }

    function sectionTitel($title = '') {

        $outx = "
<div class = 'section'>
<p class = 'title is-4'>$title</p>";
        $this->out[] .= $outx;
        $this->renderOut();
    }

    function container() {

        $outx = "<div class = 'container'>";
        $this->out[] .= $outx;
        $this->renderOut();
    }

    function content($what) {
        if ($what) {
            $this->out[] .= "<div class = 'content'> $what </div>";
        } else {
            $this->out[] .= "<div class = 'content'>";
        }
        $this->renderOut();
    }

    function closeContent() {
        $this->out[] .= "</div>";
        $this->renderOut();
    }

    function closePage() {
        $this->out[] .= "</body></html>";
        $this->renderOut();
    }

    function closeBlock() {
        $this->out[] .= "</div>";
        $this->renderOut();
    }

    function closeSection() {
        $this->out[] .= "</div>";
        $this->renderOut();
    }

    function closeContainer() {
        $this->out[] .= "</div>";
        $this->renderOut();
    }

    function renderOut() {

        echo implode('', $this->out);
        $this->out = [];
    }

//put your code here
}

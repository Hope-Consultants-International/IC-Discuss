<?php
/**
IC-Discuss (c) 2015 by Hope Consultants International Ltd.

IC-Discuss is licensed under a
Creative Commons Attribution-ShareAlike 4.0 International License.

You should have received a copy of the license along with this
work.  If not, see <http://creativecommons.org/licenses/by-sa/4.0/>.
**/
?>
<script language="javascript">
function clearAuthenticationCache(page)
{
    // Default to a non-existing page (give error 500).
    // An empty page is better, here.
    if (!page) page = '.force_logout';

    try
    {
        var agt=navigator.userAgent.toLowerCase();
        if (agt.indexOf("msie") != -1)
        {
            // IE clear HTTP Authentication
            document.execCommand("ClearAuthenticationCache");
        }
        else
        {
            var xmlhttp;
            if (window.XMLHttpRequest)
            {
                xmlhttp = new XMLHttpRequest();
            }
            else if (window.ActiveXObject)
            {
                xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
            }
            else
            {
                return;
            }

            // Let's prepare invalid credentials
            xmlhttp.open("GET", page, true, "logout", "logout");
            // Let's send the request to the server
            xmlhttp.send("");
            // Let's abort the request
            xmlhttp.abort();
        }
    }
    catch(e)
    {
        alert("An exception occurred in the script. Error name: " + e.name + ". Error message: " + e.message);
        // There was an error
        return;
    }
}

var reload_timer = null;
function reload(){
      window.location.href='<?php print($target); ?>';
}

$( document ).ready(function() {
    clearAuthenticationCache('<?php print($temp_page); ?>');
    reload_timer = setTimeout(reload, 2000);
});
</script>
<a href="<?php print($target); ?>">Logout in progress...</a>

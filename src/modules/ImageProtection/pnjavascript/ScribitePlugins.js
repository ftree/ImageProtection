//=============================================================================
// External interface functions
//=============================================================================

function ScribitePluginsImageProtectionXinha(editor, ImPrURL)
{
    // Save editor for access in selector window
    currentImPrEditor = editor;
    var StartURL = currentImPrEditor.URL; 
    
    var image = null;
    image = editor.getParentElement();
    ImPr.image = image;
    ImPr.OrigThumbWidth = image.width;
    ImPr.OrigThumbHeight = image.height;
    
    //ImPr.image = image;
    window.open(ImPrURL, "", getImPrPopupAttributes(StartURL,image.width, image.height ));
}

function ScribitePluginsImageProtectionPS(targetId, ImPrURL)
{
	currentImPrInput = document.getElementById(targetId);
    if (currentImPrInput == null)
        alert("Unknown input element '" + targetId + "'");

    window.showModalDialog(ImPrURL, "", getImPrPopupAttributes());
}

function getImPrPopupAttributes(param, width, height)
{
	var pWidth = 550;
	var pHeight = 200;
/*	
	if (width > 150) {
		pWidth = pWidth + (width-150);
	}
	if (width > 150) {
		pHeight = pHeight + (height-150) + 40 ;
	}
*/
    return "width="+pWidth+",height="+pHeight+",resizable=no,dependent=yes,location=no,status=no,toolbar=no,modal=yes";    
    //return "width="+pWidth+",height="+pHeight+",modal=yes";
}

//=============================================================================
// Internal stuff
//=============================================================================

// htmlArea 3.0 editor for access in selector window
var currentImPrEditor = null;
//var ImPrImageParams = null;
var currentImPrImage = null;

var ImPr = {}

ImPr.Init = function() {

    var outparam 		= {"editor" : currentImPrEditor, param : null};
    var image 			= window.opener.ImPr.image;
    var OrigThumbWidth 	= window.opener.ImPr.OrigThumbWidth;
    var OrigThumbHeight	= window.opener.ImPr.OrigThumbHeight;
    var baseURI 		= image.baseURI;
    var base 			= document.getElementById("f_baseURI");
    
    base.value   = baseURI;
    
    if (image && !/^img$/i.test(image.tagName))
        image = null;
//f_url    : Xinha.is_ie ? image.src : image.getAttribute("src"),
//baseHref: currentImPrEditor.config.baseHref
    if (image) {
        outparam.param = {
            f_url    : image.src,
            f_orig   : image.id,
            f_alt    : image.alt,
            f_title  : image.title,
            f_border : image.style.borderWidth ? image.style.borderWidth : image.border,
            f_align  : image.align,
            f_width  : image.width,
            f_height  : image.height,
            f_padding: image.style.padding,
            f_margin : image.style.margin,
            f_backgroundColor: image.style.backgroundColor,
            f_borderColor: image.style.borderColor
        };

        // compress 'top right bottom left' syntax into one value if possible
        //outparam.param.f_border = ImPrShortSize(outparam.param.f_border);
        //outparam.param.f_padding = ImPrShortSize(outparam.param.f_padding);
        //outparam.param.f_margin = ImPrShortSize(outparam.param.f_margin);

        // convert rgb() calls to rgb hex
        //outparam.param.f_backgroundColor = ImPrConvertToHex(outparam.param.f_backgroundColor);
        //outparam.param.f_borderColor = ImPrConvertToHex(outparam.param.f_borderColor);
	
    }
    
    var title 		= document.getElementById("f_title"); 
    var thumb 		= document.getElementById("f_preview");
    var align 		= document.getElementById("f_align");
    var orig 		= document.getElementById("f_OrigFile");
    var base 		= document.getElementById("f_baseURI");
	var ThumbWidth	= document.getElementById("f_ThumbWidth");
	var ThumbHeight  = document.getElementById("f_ThumbHeight");
	
    orig.value   = outparam.param.f_orig;
    title.value  = outparam.param.f_title;
    align.value  = outparam.param.f_align;
    thumb.src    = outparam.param.f_url;
    ThumbWidth.value = OrigThumbWidth;
    ThumbHeight.value = OrigThumbHeight;
    
    if ( OrigThumbWidth > OrigThumbHeight) {
    	thumb.width  = 150 //outparam.param.f_width;
    	thumb.height = OrigThumbHeight / (OrigThumbWidth / 150);
    } else {
    	thumb.width  = OrigThumbWidth / (OrigThumbHeight / 150);
    	thumb.height = 150;
    }

    //alert ("w:" + thumb.width + " h:" + thumb.height);
    //thumb.width  = outparam.param.f_width;
    //thumb.height = outparam.param.f_height;
    
    //document.getElementById("f_preview").src = outparam.param.f_url;
    //document.getElementById("f_align").value = outparam.param.f_align;
}

ImPr.Upload = function() {

}

ImPr.Insert = function() {
	var html = null;
	var thumb = null;
	
	var src    		= document.getElementById("f_preview").src;
	var width  		= document.getElementById("f_preview").width;
	var height 		= document.getElementById("f_preview").height;
	var align  		= document.getElementById("f_align").value;
	var title  		= document.getElementById("f_title").value;
	var extraPath  	= document.getElementById("f_extraPath").value;
	var OrigFile  	= document.getElementById("f_OrigFile").value;
	var ThumbWidth  = document.getElementById("f_ThumbWidth").value;
	var ThumbHeight	= document.getElementById("f_ThumbHeight").value;
	
	if (src == "") {
		alert ("test");	
	} else {

		//thumb = "<img class=\"ImageProtectionImage\" id=\""+extraPath+"/"+OrigFile+"\" title=\""+title+"\" alt=\""+title+"\" src=\""+src+"\" align=\""+align+"\" />";

		if (align == "left") {
			style="float: left;";
		} else if (align == "right") {
			style="float: right;";
		} else {
			style="";
		}
		if (ThumbWidth != "") {
			style = style + " width: " + ThumbWidth +"px;";
		} else {
			style = style + " width: " + width +"px;";
		}
		if (ThumbHeight != "") {
			style = style + " height: " + ThumbHeight + "px;";
		} else {
			style = style + " height: " + height + "px;";
		}
		
		thumb = "<img class=\"ImageProtectionImage\" id=\""+extraPath+"/"+OrigFile+"\" title=\""+title+"\" alt=\""+title+"\" src=\""+src+"\" align=\""+align+"\" style=\""+style+"\" />";
		html=thumb;
		
		//alert(thumb);
		//html = "<a href=\"index.php?module=ImageProtection&func=viewImage&height=" + OrigHeight + "&width=" + OrigWidth +"&src=" + OrigFile +"\" class=\"thickbox\" >";
		//html = html + thumb;
		//html = html + "</a>";

		window.opener.currentImPrEditor.focusEditor();
		window.opener.currentImPrEditor.insertHTML(html);
		window.opener.focus();
		window.close();
    	}
}

ImPr.Cancel = function() {
    window.opener.focus();
    window.close();
}


function ImPrShortSize(cssSize)
{
    if(/ /.test(cssSize))
    {
        var sizes = cssSize.split(' ');
        var useFirstSize = true;
        for(var i = 1; i < sizes.length; i++)
        {
            if(sizes[0] != sizes[i])
            {
                useFirstSize = false;
                break;
            }
        }
        if(useFirstSize) cssSize = sizes[0];
    }

    return cssSize;
}

function ImPrConvertToHex(color) {

    if (typeof color == "string" && /, /.test.color)
        color = color.replace(/, /, ','); // rgb(a, b) => rgb(a,b)

    if (typeof color == "string" && / /.test.color) { // multiple values
        var colors = color.split(' ');
        var colorstring = '';
        for (var i = 0; i < colors.length; i++) {
            colorstring += Xinha._colorToRgb(colors[i]);
            if (i + 1 < colors.length)
                colorstring += " ";
        }
        return colorstring;
    }

    return Xinha._colorToRgb(color);
}

// ImageProtection Image Upload plugin for Xinha
// developed by Tree Florian
//
// requires ImageProtection module , licensed under GPL
//
// Distributed under the same terms as xinha itself.
// This notice MUST stay intact for use (see license.txt).

function ImageProtectionImages(editor) {
    this.editor = editor;
    var cfg = editor.config;
    var self = this;

    cfg.registerButton({
        id       : "ImageProtectionImages",
        tooltip  : "Insert Image",
        image    : editor.imgURL("btn_open1.gif", "ImageProtectionImages"),
        textMode : false,
        action   : function(editor) {
                    	url = document.location.pnbaseURL + document.location.entrypoint + "?module=ImageProtection&type=plugins&func=Images";
						ScribitePluginsImageProtectionXinha(editor, url);
        		   }
    })
    cfg.addToolbarElement("ImageProtectionImages", "insertimage", 1);
}
ImageProtectionImages._pluginInfo = {
    name          : "ImageProtection Image Upload for xinha",
    version       : "1.0",
    developer     : "Florian Tree",
    developer_url : "http://code.zikula.org/imageprotection/",
    sponsor       : "Florian Tree",
    sponsor_url   : "http://code.zikula.org/imageprotection/",
    license       : "none"
};


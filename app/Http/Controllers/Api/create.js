app.preferences.rulerUnits = Units.PIXELS

var doc = app.documents.add();
doc.resizeImage(750 * 4, 1334 *4);

function insertLayer(fileName, layerName, x, y, scale) {
    var img = new File(fileName);
    var opened = open(img);

    app.activeDocument = opened;

    executeAction(stringIDToTypeID("newPlacedLayer"), undefined, DialogModes.NO);

    var height = opened.height * 4 * (scale / 100);
    var width = opened.width * 4 * (scale / 100);

    var oX = opened.artLayers[0].bounds[0];
    var oY = opened.artLayers[0].bounds[1];

    opened.resizeImage(width, height);
    opened.artLayers[0].duplicate(doc);
    opened.close(SaveOptions.DONOTSAVECHANGES);

    doc.artLayers[0].name = layerName;

    // x = x * 4 - width / 2 + oX * 4;
    // y = y * 4 - height / 2 + oY * 4;

    x = x * 4 - width / 2 + oX;
    y = y * 4 - height / 2 + oY;

    MoveLayerTo(doc.artLayers[0], x, y);
}

var folder = File($.fileName).parent.fsName;

collectFiles(folder + '/items');

function MoveLayerTo(fLayer, fX, fY) {
    var Position = fLayer.bounds;
    Position[0] = fX - Position[0]
    Position[1] = fY - Position[1]

    fLayer.translate(-(Position[0]), -(Position[1]));
}

function collectFilesFromFolder(folderPath) {
    var selectedFolder = Folder(folderPath);
    if (selectedFolder == null)
        return;

    var fileList = selectedFolder.getFiles();

    if (fileList.length > 0) {
        for (var i in fileList) {
            var fileName = decodeURI(fileList[i].name);
            var posX = 0;
            var posY = 0;
            var scale = 100;

            for (var j in settings) {
                if (settings[j].image == fileName) {
                    posX = settings[j].pos_x;
                    posY = settings[j].pos_y;
                    scale = settings[j].scale;
                    break;
                }
            }

            insertLayer(folderPath + '/' + fileName, fileName, posX, posY, scale);
        }
    }
};

function collectFiles(folderPath) {
    for(var j in settings) {
        posX = settings[j].pos_x;
        posY = settings[j].pos_y;
        scale = settings[j].scale;
        try {
            insertLayer(folderPath + '/' + settings[j].image, settings[j].image, posX, posY, scale);
        } catch (e) {

        }
    }
}

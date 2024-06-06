$(document).ready(function() {

    previewDivs = document.querySelectorAll('#preview-container');
    if (previewDivs.length == 1) {

        var parent_back_layer = document.querySelector('.parent-back-layer');
        var parent_right_layer = document.querySelector('.parent-right-layer');
        var parent_mid_layer = document.querySelector('.parent-mid-layer');
        var parent_left_layer = document.querySelector('.parent-left-layer');
        var parent_front_layer = document.querySelector('.parent-front-layer');


        var back_layers = document.querySelectorAll('.back-layer');
        var right_layers = document.querySelectorAll('.right-layer');
        var mid_layers = document.querySelectorAll('.mid-layer');
        var left_layers = document.querySelectorAll('.left-layer');
        var front_layers = document.querySelectorAll('.front-layer');


        back_layers.forEach(function (back_layer) {
            parent_back_layer.appendChild(back_layer);
        });
        right_layers.forEach(function (right_layer) {
            parent_right_layer.appendChild(right_layer);
        });
        mid_layers.forEach(function (mid_layer) {
            parent_mid_layer.appendChild(mid_layer);
        });
        left_layers.forEach(function (left_layer) {
            parent_left_layer.appendChild(left_layer);
        });
        front_layers.forEach(function (front_layer) {
            parent_front_layer.appendChild(front_layer);
        });

    }else{
        setTimeout(function() {
            var previewDivs = document.querySelectorAll('.preview-container');
            //console.log(previewDivs)
            if (previewDivs.length > 0) {
                previewDivs.forEach(function (previewDiv) {

                    var parent_back_layer = previewDiv.querySelector('.parent-back-layer');
                    var parent_right_layer = previewDiv.querySelector('.parent-right-layer');
                    var parent_mid_layer = previewDiv.querySelector('.parent-mid-layer');
                    var parent_left_layer = previewDiv.querySelector('.parent-left-layer');
                    var parent_front_layer = previewDiv.querySelector('.parent-front-layer');


                    var back_layers = previewDiv.querySelectorAll('.back-layer');
                    var right_layers = previewDiv.querySelectorAll('.right-layer');
                    var mid_layers = previewDiv.querySelectorAll('.mid-layer');
                    var left_layers = previewDiv.querySelectorAll('.left-layer');
                    var front_layers = previewDiv.querySelectorAll('.front-layer');

                    back_layers.forEach(function (back_layer) {
                        parent_back_layer.appendChild(back_layer);
                    });
                    right_layers.forEach(function (right_layer) {
                        parent_right_layer.appendChild(right_layer);
                    });
                    mid_layers.forEach(function (mid_layer) {
                        parent_mid_layer.appendChild(mid_layer);
                    });
                    left_layers.forEach(function (left_layer) {
                        parent_left_layer.appendChild(left_layer);
                    });
                    front_layers.forEach(function (front_layer) {
                        parent_front_layer.appendChild(front_layer);
                    });
                });

            }
        }, 2000);

    }

});




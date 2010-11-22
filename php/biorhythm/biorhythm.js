/* * * * * * * * * * * *

Licenced for use under the LGPL. See http://www.gnu.org/licenses/lgpl-3.0.txt.

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This licence is there: http://www.gnu.org/licenses/lgpl-3.0.txt.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS /FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

* * * * * * * * * * * * */

/**
* @author       GG Network S.A.
* @copyright    Copyright © 2010, GG Network S.A.
* @license      Licenced for use under the LGPL. See http://www.gnu.org/licenses/lgpl-3.0.txt
*/

function biorhythm_init(biorhythms, title, app_url, image_url){

    $('.friends img').click(function(event){
        $.ajax({
          url: "send_friend.php?id="+$(event.target).attr('id'),
          cache: false,
          success: function(html){
            $("#results").html(html);
          }
        });
    });
    var values = $.map(biorhythms,function(biorhythm){ return biorhythm.value; });
    var min = Math.min.apply(this,values);
    var max = Math.max.apply(this,values);

    $(biorhythms).each(function(index, biorhythm){
        var el = $('.bio_result').eq(index).children();
        el.addClass(biorhythm.value > 0 ? 'bio_up' : 'bio_down');
        el.addClass(biorhythm.value == min ? 'box_min' : biorhythm.value == max ? 'box_max' : 'box_normal');
        el.add($('.publish_button input').eq(index)).click(function(){
            GGAPI.addStream({
                title: title + ' ' + biorhythm.name + ' ' +  biorhythm.value + '%',
                image: image_url + biorhythm.image,
                content: biorhythm.description,
                link: app_url
            });
        });
        // publikuj domyślnie maksymalny
        if( biorhythm.value == max){
            setTimeout(function(){
                GGAPI.addStream({
                    title: title + ' ' + biorhythm.name + ' ' +  biorhythm.value + '%',
                    image: image_url + biorhythm.image,
                    content: biorhythm.description,
                    link: app_url
                });
            }, 2100);
        }
        el.animate(
            {
                height: 100 + biorhythm.value + 'px'
            },
            {
                step: function(step, data){
                    el.html('<em>'+ biorhythm.name + ' ' + Math.floor(step / data.end * biorhythm.value) + '%</em>');
                },
            duration: 2100
        });
    });
}

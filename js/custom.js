jQuery(document).ready(function(){
    jQuery('body').on('click', '#selectall', function() {
        jQuery('.singlechekbox').prop('checked', this.checked);
  });

  jQuery('body').on('click', '.singlechekbox', function() {
      if(jQuery(".singlechekbox").length == jQuery(".singlechekbox:checked").length) {
        jQuery("#selectall").prop("checked", "checked");
      } else {
        jQuery("#selectall").removeAttr("checked");
      }

  });
jQuery("#apply").on("click", function(event){
var action = jQuery("#bulkopt").val();
if (action === 'grab'){
    //alert(action);
    var ids = [];
    var kws = [];
    var bulk = 0;
    //var idskws = {};
    jQuery(".fixed-wrap").show();
    jQuery(".close").click(function(){
        window.location.replace(window.location.href);
    });
    jQuery(".singlechekbox:checked").each(function(){
        if (jQuery(this).is(':checked')) {
        //console.log(jQuery(this).val());
        ids.push(jQuery(this).val())
        kws.push(jQuery(this).attr("data-kw"));
        }
    });
    // for (var i=0, j=ids.length; i<j; i++){
    //     idskws[ids[i]] = kws[i];
    // }
    bulk_postkw();
    
    jQuery("#loader").show();
    jQuery("#done").hide();

    function bulk_postkw() {
        //console.log('key: ' + key + '\n' + 'value: ' + element + '\n' + 'index: ' + i);
        var posted = 0;
        if (bulk < ids.length ){
            //console.log(i);
            jQuery.ajax({
                type: 'POST', // Adding Post method
                url: MyAjax.ajaxurl, // Including ajax file
                //dataType : 'json',
                data: {
                    "action":"bulk_grab",
                    "id":ids[bulk],
                    //"kw": idskws[key],
                    //"idx": i,
                },
                beforeSend: function () {
                    jQuery("#loader").html("<span>Creating post </span><span id='ballsWaveG'><span id='ballsWaveG_5' class='ballsWaveG'></span><span id='ballsWaveG_6' class='ballsWaveG'></span><span id='ballsWaveG_7' class='ballsWaveG'></span><span id='ballsWaveG_8' class='ballsWaveG'></span></span><span style='padding-left: 50px;'>" + kws[bulk] +  "</p>");
                    },
                success: function(response){
                    
                    var data = jQuery.parseJSON(response);
                    console.log(data);
                    jQuery("#sukses").prepend("<li class='item-posted' id='item-posted-"+data.id+"'><h3 class='title-posted'>"+kws[bulk - 1]+ "</h3><span class='posted'></span><div class='sukses-wrapper'><span class='sukses sukses-"+data.id+"'></span></div></li>");
                    var i = 0;
                    if (!data.imgsrc){
                        bulk_postkw();
                    } else {
                    var l = data.imgsrc.length;
                    console.log(l);
                   // jQuery(data.imgsrc).each (function(i, value) {
                    function bulk_insert_image(){
                        if ( i < l ) {
                            jQuery.ajax({
                                type: 'POST',
                                url: MyAjax.ajaxurl,
                                //datatype:'json',
                                //async:false,
                                data: {
                                    "action": "download_image",
                                    "id":data.id,
                                    "kw":data.kw,
                                    "imgsrc":data.imgsrc[i],
                                    "imgtitle":data.imgtitle[i],
                                    "imgthumb":data.imgthumb[i],
                                    "idx":i,
                                    "domain":data.host[i],
                                    "imglink":data.imglink[i],
                                },
                                beforeSend: function () {
                                    //i++;
                                    jQuery("#item-posted-"+data.id).append("<div id='panel-"+data.id+'-'+i+"' class='panel'><img src='"+data.imgthumb[i]+"'>Title : "+data.imgtitle[i]+"<br>Status: <span class='label'><span class='grabbing'><i class='icon-spin4 animate-spin'></i>Grabbing</span></span><br>Source: <a href='"+data.imglink[i]+"'>"+data.host[i]+"</a></div>");
                                },
                                success: function(response){
                                    i++;
                                    //console.log("#panel-"+data.id+'-'+(i-1));
                                    var resp = jQuery.parseJSON(response);
                                    //console.log(resp);
                                    console.log(i);
                                    //if(data.response==1){
                                    jQuery("#panel-"+data.id+'-'+(i-1)).find("span.label").html("<span class='downloaded'>"+resp.desc+"</span>");
                                    bulk_insert_image();
                                },
                                error:function(){
                                    i++;
                                    jQuery("#panel-"+data.id+'-'+(i-1)).find("span.label").html("<span class='fail'>Fail</span>");
                                    //return jQuery.Deferred().resolve();
                                    bulk_insert_image();
                                },
                                complete: function (jqXHR, textStatus){
                                    //i++;
                                    //console.log("#panel-"+data.id+'-'+(i-1));
                                    
                                    posted = posted + 1;
                                    //console.log(posted);
                                    //console.log(l);
                                    var persen = Math.round((posted / (l)) * 100);
                                    jQuery("span.sukses.sukses-"+data.id+"").css("width", persen+"%");
                                    if (posted === (l)){
                                        jQuery.ajax({
                                            type: 'POST',
                                            url: MyAjax.ajaxurl,
                                            data:{
                                                "action": "count_image_posted",
                                                "id": data.id,
                                                "kw":data.kw,
                                                "imgsrc":data.imgsrc,
                                                "imgtitle":data.imgtitle,
                                            },
                                            success: function(response){
                                                var resp = jQuery.parseJSON(response);
                                                //console.log("from count image posted: "+response);
                                                jQuery("#item-posted-"+data.id).find("span.posted").html(" | ("+resp.count+" images) <a href='"+resp.link+"' target='_blank'>View post</a><span class='toggle'><i class='icon-plus-circled'></i></span>");
                                                jQuery("#item-posted-"+data.id).find("div.panel").hide(250);
                                                jQuery("#item-posted-"+data.id).find("span.sukses").css("box-shadow","none");
                                            },
                                        })
                                    }
                                    if (i === data.imgsrc.length) {
                                        bulk_postkw();
                                    }
                                },
                                timeout: 5000,
                            });
                            //i++; 
                        }
                    }
                    bulk_insert_image();
                    }
                },
                error:function(){
                    bulk_postkw();
                },
                timeout: 5000,
                // complete: function (jqXHR, textStatus){
                //     //if (i === 4) {
                //         bulk_postkw();
                //        // }
                //     //i++;
                // }
            });
        }
        else {
            jQuery("#loader").hide();
            jQuery("#done").show();
            // jQuery("#bulk_post_titles option").click(function(event){
            //     jQuery("#bulk_post_titles option:selected").attr('disabled','disabled')
            // });
        }
        bulk++;
    }
}
if (action === 'delete-image'){
    //alert(action);
    var ids = [];
    var kws = [];
    var bulk = 0;
    jQuery(".fixed-wrap").show();
    jQuery(".close").click(function(){
        window.location.replace(window.location.href);
    });
    jQuery(".singlechekbox:checked").each(function(){
        if (jQuery(this).is(':checked')) {
        //console.log(jQuery(this).val());
        ids.push(jQuery(this).val());
        kws.push(jQuery(this).attr("data-kw"));
        }
    });

    jQuery("#loader").show();
    jQuery("#done").hide();

    jQuery.ajax({
        type:'POST',
        url:MyAjax.ajaxurl,
        data: {
            "action":"bulk_delete_image",
            "ids":ids,
        },
        beforeSend: function () {
            jQuery("#loader").html("<span>Deleting post </span><span id='ballsWaveG'><span id='ballsWaveG_5' class='ballsWaveG'></span><span id='ballsWaveG_6' class='ballsWaveG'></span><span id='ballsWaveG_7' class='ballsWaveG'></span><span id='ballsWaveG_8' class='ballsWaveG'></span></span><span style='padding-left: 50px;'>Processing...</p>");
            },
        success: function(response){
            console.log(response);
            jQuery("#loader").hide();
            jQuery("#done").show();
            jQuery("#sukses").append(response);
        }
    });
}
if(action === 'delete-post'){
    //alert(action);
    var ids = [];
    jQuery(".fixed-wrap").show();
    jQuery(".close").click(function(){
        window.location.replace(window.location.href);
    });
    jQuery(".singlechekbox:checked").each(function(){
        if (jQuery(this).is(':checked')) {
        //console.log(jQuery(this).val());
        ids.push(jQuery(this).val());
        }
    });
    
    jQuery("#loader").show();
    jQuery("#done").hide();

    jQuery.ajax({
        type:'POST',
        url:MyAjax.ajaxurl,
        data: {
            "action":"bulk_delete_post",
            "ids":ids,
        },
        beforeSend: function () {
            jQuery("#loader").html("<span>Deleting post </span><span id='ballsWaveG'><span id='ballsWaveG_5' class='ballsWaveG'></span><span id='ballsWaveG_6' class='ballsWaveG'></span><span id='ballsWaveG_7' class='ballsWaveG'></span><span id='ballsWaveG_8' class='ballsWaveG'></span></span><span style='padding-left: 50px;'>Processing...</p>");
            },
        success: function(response){
            console.log(response);
            jQuery("#loader").hide();
            jQuery("#done").show();
            jQuery("#sukses").append(response);
        }
    });
}
if(action === 'delete-post-image'){
   // alert(action);
   var ids = [];
   jQuery(".fixed-wrap").show();
   jQuery(".close").click(function(){
       window.location.replace(window.location.href);
   });
   jQuery(".singlechekbox:checked").each(function(){
       if (jQuery(this).is(':checked')) {
       //console.log(jQuery(this).val());
       ids.push(jQuery(this).val());
       }
   });
   
   jQuery("#loader").show();
   jQuery("#done").hide();

   jQuery.ajax({
       type:'POST',
       url:MyAjax.ajaxurl,
       data: {
           "action":"bulk_delete_post_and_image",
           "ids":ids,
       },
       beforeSend: function () {
           jQuery("#loader").html("<span>Deleting post </span><span id='ballsWaveG'><span id='ballsWaveG_5' class='ballsWaveG'></span><span id='ballsWaveG_6' class='ballsWaveG'></span><span id='ballsWaveG_7' class='ballsWaveG'></span><span id='ballsWaveG_8' class='ballsWaveG'></span></span><span style='padding-left: 50px;'>Processing...</p>");
           },
       success: function(response){
           console.log(response);
           jQuery("#loader").hide();
           jQuery("#done").show();
           jQuery("#sukses").append(response);
       }
   });
}

});


    jQuery("body").on("click", "#one-grab", function(e){
        e.preventDefault();
        jQuery(".fixed-wrap").show();
        jQuery(".close").click(function(){
            window.location.replace(window.location.href);
        });
        var kw = jQuery(this).parents("tr").find("#kw").text();
        var id = jQuery(this).parents("tr").find("#post-id").text();

        //bulk_postkw();
    
        jQuery("#loader").show();
        jQuery("#done").hide();
        var posted = 0;
        jQuery.ajax({
            type:'POST',
            url: MyAjax.ajaxurl,
            data: {
                "action": "one_grab",
                "kw": kw,
                "id": id
            },
            beforeSend: function () {
                jQuery("#loader").html("<span>Creating post </span><span id='ballsWaveG'><span id='ballsWaveG_5' class='ballsWaveG'></span><span id='ballsWaveG_6' class='ballsWaveG'></span><span id='ballsWaveG_7' class='ballsWaveG'></span><span id='ballsWaveG_8' class='ballsWaveG'></span></span><span style='padding-left: 50px;'>" + kw +  "</p>");
                },
                success: function(response){
                    var data = jQuery.parseJSON(response);
                    //console.log(data);
                    jQuery("#sukses").prepend("<li class='item-posted' id='item-posted-"+data.id+"'><h3 class='title-posted'>"+kw+ "</h3><span class='posted'></span><div class='sukses-wrapper'><span class='sukses sukses-"+data.id+"'></span></div></li>");
                    var i = 0;
                    if (!data.imgsrc){
                        jQuery("#item-posted-"+data.id).find("span.posted").html(" | (0 images) <span class='toggle'><i class='icon-plus-circled'></i></span>");
                    } else {
                    var l = data.imgsrc.length;
                    //console.log(l);
                   // jQuery(data.imgsrc).each (function(i, value) {
                    function bulk_insert_image(){
                        if ( i < l ) {
                            jQuery.ajax({
                                type: 'POST',
                                url: MyAjax.ajaxurl,
                                //datatype:'json',
                                //async:false,
                                data: {
                                    "action": "download_image",
                                    "id":data.id,
                                    "kw":data.kw,
                                    "imgsrc":data.imgsrc[i],
                                    "imgtitle":data.imgtitle[i],
                                    "imgthumb":data.imgthumb[i],
                                    "idx":i,
                                    "domain":data.host[i],
                                    "imglink":data.imglink[i],
                                },
                                beforeSend: function () {
                                    //i++;
                                    jQuery("#item-posted-"+data.id).append("<div id='panel-"+data.id+'-'+i+"' class='panel'><img src='"+data.imgthumb[i]+"'>Title : "+data.imgtitle[i]+"<br>Status: <span class='label'><span class='grabbing'><i class='icon-spin4 animate-spin'></i>Grabbing</span></span><br>Source: <a href='"+data.imglink[i]+"'>"+data.host[i]+"</a></div>");
                                },
                                success: function(response){
                                    i++;
                                    //console.log("#panel-"+data.id+'-'+(i-1));
                                    var resp = jQuery.parseJSON(response);
                                    //console.log(resp);
                                    //console.log(i);
                                    //if(data.response==1){
                                    jQuery("#panel-"+data.id+'-'+(i-1)).find("span.label").html("<span class='downloaded'>"+resp.desc+"</span>");
                                    bulk_insert_image();
                                },
                                error:function(){
                                    i++;
                                    jQuery("#panel-"+data.id+'-'+(i-1)).find("span.label").html("<span class='fail'>Fail</span>");
                                    //return jQuery.Deferred().resolve();
                                    bulk_insert_image();
                                },
                                complete: function (jqXHR, textStatus){
                                    //i++;
                                    //console.log("#panel-"+data.id+'-'+(i-1));
                                    
                                     posted = posted + 1;
                                     console.log(posted);
                                    console.log(l);
                                    var persen = Math.round((i / (l)) * 100);
                                    jQuery("span.sukses.sukses-"+data.id+"").css("width", persen+"%");
                                    if (i === (l)){
                                        jQuery.ajax({
                                            type: 'POST',
                                            url: MyAjax.ajaxurl,
                                            data:{
                                                "action": "count_image_posted",
                                                "id": data.id,
                                                "kw":data.kw,
                                                "imgsrc":data.imgsrc,
                                                "imgtitle":data.imgtitle,
                                            },
                                            success: function(response){
                                                var resp = jQuery.parseJSON(response);
                                                //console.log("from count image posted: "+response);
                                                jQuery("#item-posted-"+data.id).find("span.posted").html(" | ("+resp.count+" images) <a href='"+resp.link+"' target='_blank'>View post</a><span class='toggle'><i class='icon-plus-circled'></i></span>");
                                                jQuery("#item-posted-"+data.id).find("div.panel").hide(250);
                                                jQuery("#item-posted-"+data.id).find("span.sukses").css("box-shadow","none");
                                            },
                                        })
                                    }
                                    if (posted === l){
                                        jQuery("#done").show();
                                    }
                                },
                                timeout: 5000,
                            });
                            //i++; 
                        }
                    }
                    bulk_insert_image();
                    }
                },
                complete: function (jqXHR, textStatus){
                    jQuery("#loader").hide();
                    
                }
        })

    });
    jQuery("#bulk-post").click(function(){
        var bulk_post_titles = jQuery("#bulk_post_titles").val();
        var current = 0;
        var total = bulk_post_titles.length;
        var category = jQuery("#category").val();
        var bulk_post_status = jQuery("#bulk_post_status").val();
        var date_day = jQuery("#date_day").val();
        var date_month = jQuery("#date_month").val();
        var date_year = jQuery("#date_year").val();
        var interval_num = jQuery("#interval_num").val();
        var interval_type = jQuery("#interval_type").val();
        
        postKw();
        
        jQuery("#loader").show();
        jQuery("#done").hide();
        function postKw() {
            var posted = 0;
            if (current < total) {
            jQuery.ajax({
            type: 'POST', // Adding Post method
            url: MyAjax.ajaxurl, // Including ajax file
            //dataType : 'json',
            data: {
                "action": "create_post",
                "bulk_post_title":bulk_post_titles[current],
                "current":current,
                "total":total,
                "category":category,
                "bulk_post_status":bulk_post_status,
                "date_day":date_day,
                "date_month":date_month,
                "date_year":date_year,
                "interval_num":interval_num,
                "interval_type":interval_type,
                },
            beforeSend: function () {
                jQuery("#loader").html("<span>Creating post </span><span id='ballsWaveG'><span id='ballsWaveG_5' class='ballsWaveG'></span><span id='ballsWaveG_6' class='ballsWaveG'></span><span id='ballsWaveG_7' class='ballsWaveG'></span><span id='ballsWaveG_8' class='ballsWaveG'></span></span><span style='padding-left: 50px;'>" + bulk_post_titles[current] +  "</p>");
                },
            success: function(response){
                //console.log(response);
                //jQuery("#sukses").append(response);
                var data = jQuery.parseJSON(response);
                console.log(data);
                jQuery("#sukses").prepend("<li class='item-posted' id='item-posted-"+data.id+"'><h3 class='title-posted'>"+bulk_post_titles[current - 1]+ "</h3><span class='posted'></span><div class='sukses-wrapper'><span class='sukses sukses-"+data.id+"'></span></div></li>");
                var i = 0;
                if (!data.imgsrc){
                    jQuery("#item-posted-"+data.id).find("span.posted").html(" | (0 images) <span class='toggle'><i class='icon-plus-circled'></i></span>");
                    postKw();
                    jQuery.ajax({
                        type: 'POST',
                        url: MyAjax.ajaxurl,
                        data:{
                            "action": "zero_image_posted",
                            "id": data.id,
                        },
                        success: function(response){
                            console.log(response);
                        }
                    });
                } else {
                    var l = data.imgsrc.length;
                // jQuery(data.imgsrc).each (function(i, value) {
                    function next(){
                    if ( i < l ) {
                        jQuery.ajax({
                            type: 'POST',
                            url: MyAjax.ajaxurl,
                            //datatype:'json',
                            //async:false,
                            data: {
                                "action": "download_image",
                                "id":data.id,
                                "kw":data.kw,
                                "imgsrc":data.imgsrc[i],
                                "imgtitle":data.imgtitle[i],
                                "imgthumb":data.imgthumb[i],
                                "idx":i,
                                "domain":data.host[i],
                                "imglink":data.imglink[i],
                            },
                            beforeSend: function () {
                                //i++;
                                jQuery("#item-posted-"+data.id).append("<div id='panel-"+data.id+'-'+i+"' class='panel'><img src='"+data.imgthumb[i]+"'>Title : "+data.imgtitle[i]+"<br>Status: <span class='label'><span class='grabbing'><i class='icon-spin4 animate-spin'></i>Grabbing</span></span><br>Source: <a href='"+data.imglink[i]+"'>"+data.host[i]+"</a></div>");
                            },
                            success: function(response){
                                i++;
                                //console.log(response);
                                var resp = jQuery.parseJSON(response);
                                //console.log(resp);
                                //console.log(i);
                                //if(data.response==1){
                                    jQuery("#panel-"+data.id+'-'+(i-1)).find("span.label").html("<span class='downloaded'>"+resp.desc+"</span>");
                                    next();
                            // }else{
                                //   jQuery("#panel-"+data.current+'-'+(i-1)).find("span.label").html("<span class='downloaded'>Downloaded</span>");
                                //  next();
                            // }
                            },
                            error:function(){
                                i++;
                                jQuery("#panel-"+data.id+'-'+(i-1)).find("span.label").html("<span class='fail'>Fail</span>");
                                //return jQuery.Deferred().resolve();
                                next();
                            },
                            complete: function (jqXHR, textStatus){
                                //i++;
                                //console.log("#panel-"+data.id+'-'+(i-1));
                                //console.log(data.id);
                                posted = posted + 1;
                                //console.log(posted);
                                //console.log(l);
                                var persen = Math.round((posted / (l)) * 100);
                                jQuery("span.sukses.sukses-"+data.id+"").css("width", persen+"%");
                                if (posted === (l)){
                                    jQuery.ajax({
                                        type: 'POST',
                                        url: MyAjax.ajaxurl,
                                        data:{
                                            "action": "count_image_posted",
                                            "id": data.id,
                                            "kw":data.kw,
                                            "imgsrc":data.imgsrc,
                                            "imgtitle":data.imgtitle,
                                        },
                                        success: function(response){
                                            var resp = jQuery.parseJSON(response);
                                            //console.log("from count image posted: "+response);
                                            //console.log(resp);
                                            jQuery("#item-posted-"+data.id).find("span.posted").html(" | ("+resp.count+" images) <a href='"+resp.link+"' target='_blank'>View post</a><span class='toggle'><i class='icon-plus-circled'></i></span>");
                                            jQuery("#item-posted-"+data.id).find("div.panel").hide(250);
                                            jQuery("#item-posted-"+data.id).find("span.sukses").css("box-shadow","none");
                                        },
                                    })
                                }
                                if (i === data.imgsrc.length) {
                                postKw();
                                }
                            },
                            timeout: 5000,
                        });
                        //i++; 
                    }
                    }
                    next();
                }
            },
            error:function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.statusText);
                jQuery("#sukses").prepend("<li class='item-posted'><h3 class='title-posted'>"+bulk_post_titles[current - 1]+ "</h3> - Fail to create post.<span class='posted'></span><div class='sukses-wrapper'><span class='sukses'></span></div></li>");
                //if (jqXHR.statusText === "timeout"){
                    postKw();
                //}
                //alert ("error");
                //current = total;
                //return;

                },
                timeout: 5000,
            });
            current++;
            }
            else {
                jQuery("#loader").hide();
                jQuery("#done").show();
                // jQuery("#bulk_post_titles option").click(function(event){
                //     jQuery("#bulk_post_titles option:selected").attr('disabled','disabled')
                // });
            }

        }

    });

    jQuery("#save_kw").click(function(){
        var add_kw = jQuery("#add_kw").val();
        jQuery.ajax({
        type: 'POST', // Adding Post method
        url: MyAjax.ajaxurl, // Including ajax file
        data: {
            "action": "save_kw", 
            "add_kw":add_kw
            }, // Sending data dname to post_word_count function.
        success: function(data){ // Show returned data using the function.
        alert(data);
        location.reload();
        }
        });
    });

    jQuery("#delete_kw").click(function(){
        var add_kw = jQuery("#delete_kw").val();
        jQuery.ajax({
        type: 'POST', // Adding Post method
        url: MyAjax.ajaxurl, // Including ajax file
        data: {
            "action": "delete_kw", 
            //"add_kw":add_kw
            }, // Sending data dname to post_word_count function.
        success: function(data){ // Show returned data using the function.
        jQuery("#kw_list").val("");
        alert(data);
        location.reload();
        }
        });
    });

    jQuery("#save_template").click(function(){
        var post_template = jQuery("#post_template").val();
        var title_template = jQuery("#title_template").val();
        var attachment_title_template = jQuery("#attachment_title_template")
        .val();
        var attachment_filename_template = jQuery("#attachment_filename_template").val();
        var attachment_caption_template = jQuery("#attachment_caption_template").val();
        var attachment_description_template = jQuery("#attachment_description_template").val();
        jQuery.ajax({
        type: 'POST', // Adding Post method
        url: MyAjax.ajaxurl, // Including ajax file
        data: {
            "action": "save_template", 
            "post_template":post_template,
            "title_template":title_template,
            "attachment_title_template":attachment_title_template,
            "attachment_filename_template":attachment_filename_template,
            "attachment_caption_template":attachment_caption_template,
            "attachment_description_template":attachment_description_template,
            }, // Sending data dname to post_word_count function.
        success: function(data){ // Show returned data using the function.
        alert(data);
        location.reload();
        }
        });
    });
    jQuery("#save_setting").click(function(){
        var search_engine = jQuery("#search_engine").val();
        var num_image = jQuery("#num_image").val();
        var img_size = jQuery("#img_size").val();
        if (jQuery("#reset_img_metadata").is(":checked")) {  
            var reset_img_metadata = 1;
        } else {
            var reset_img_metadata = 0;
        }
        if (jQuery("#save_image").is(":checked")) {  
            var save_image = 1;
        } else {
            var save_image = 0;
        }
        if (jQuery("#save_mode").is(":checked")) {  
            var save_mode = 1;
        } else {
            var save_mode = 0;
        }
        var save_image_as = jQuery("#save_image_as").val();
        var image_license = jQuery("#image_license").val();
        var target_site = jQuery("#target_site").val();
        var exclude_site = jQuery("#exclude_site").val();
        var cron_category = jQuery("#cron_category").val();
        var date_day = jQuery("#date_day").val();
        var date_month = jQuery("#date_month").val();
        var date_year = jQuery("#date_year").val();
        var interval_num = jQuery("#interval_num").val();
        var interval_type = jQuery("#interval_type").val();
        var cron_post_status = jQuery("#cron_post_status").val();
        var sip_cron_kw_loop = jQuery("#sip_cron_kw_loop").val();
        if (jQuery("#sip_cron_kw_loop").is(":checked")) {  
            var sip_cron_kw_loop = 1;
        } else {
            var sip_cron_kw_loop = 0;
        }
        //console.log(save_mode);
        jQuery.ajax({
        type: 'POST', // Adding Post method
        url: MyAjax.ajaxurl, // Including ajax file
        data: {
            "action": "save_settings",
            "search_engine":search_engine,
            "num_image":num_image,
            "img_size":img_size,
            "save_image":save_image,
            "save_mode":save_mode,
            "reset_img_metadata":reset_img_metadata,
            "save_image_as":save_image_as,
            "image_license":image_license,
            "target_site":target_site,
            "exclude_site":exclude_site,
            "cron_category":cron_category,
            "date_day":date_day,
            "date_month":date_month,
            "date_year":date_year,
            "interval_num":interval_num,
            "interval_type":interval_type,
            "cron_post_status":cron_post_status,
            "sip_cron_kw_loop":sip_cron_kw_loop
            }, // Sending data dname to post_word_count function.
        success: function(data){ // Show returned data using the function.
            //jQuery("#saved").html(data);
           alert(data);
           location.reload();
        }
        });
    });
    
    jQuery("body").on("click", ".item-posted", function () {
        jQuery(this).children("div.panel").toggle(250);
        // console.log("this has been clicked");
        // alert("helo");
    });
    
    jQuery("li.item-posted").on("click", function (event) {
        event.stopPropagation();
    });

    jQuery("#remove_duplicate").click(function(){
        var kwlist = jQuery("#add_kw").val();
        var result = kwlist.split('\n').filter((word, i, arr) => arr.indexOf(word) === i);
        //console.log(result);
        jQuery("#add_kw").val(result.join('\n'));
        jQuery.ajax({
            type: 'POST', // Adding Post method
            url: MyAjax.ajaxurl, // Including ajax file
            data: {
                "action":"find_duplicate",
                "kws":result,
            },
            success: function(data){
                console.log(data);
                jQuery("#add_kw").val(data);
            }
        });
        

    });
 
    jQuery('.allpost').click(function() {
        jQuery('.allpost').addClass("active");
        jQuery('.fewpost').removeClass("active");
    });
    
    jQuery('.fewpost').click(function() {
        jQuery('.fewpost').addClass("active");
        jQuery('.allpost').removeClass("active");
    });
    
});
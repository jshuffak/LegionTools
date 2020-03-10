$(document).ready(function() {
    var worker = gup("workerId");
    var assignment = gup("assignmentId");
    var task = gup('task') ? gup('task') : "default";

    var isBanned = false;
    //checks if worker is banned
    $.ajax({
        async: false,
        url: "php/checkBanned.php",
        data: {workerId: worker, dbName: gup('dbName')},
        dataType: "text",
        success: function(d){
            if(d > 0){
                isBanned = true;
                //alert("Sorry, there is no more tasks left for you.\nPlease come later! Thank you!");//You are banned from this task.");
                window.location = "https://www.socialworldsresearch.com/LegionTools/noMoreTasks.html";
            }
            else{
                isBanned = false;
            }
        },
        fail: function(){
            alert("isBanned failed!");
        },
    });

    var isAllowed = true;
    // checks if worker is already waiting for another task
    $.ajax({
        async: false,
        url: "php/isWorkerActive.php",
        data: {workerId: worker, dbName: gup('dbName')},
        dataType: "text",
        success: function(d){
            // alert(d);
            if(d == 1){
                isAllowed = false;
                alert("You are already waiting for another task.");
                $("body").html("<h3>Sorry, you cannot wait for multiple tasks at the same time.</h3>")
            }
            else{
                isAllowed = true;
            }
        },
        fail: function(){
            alert("isWorkerActive failed!");
        },
    });
    //alert("1 before stop");
    if( assignment != "ASSIGNMENT_ID_NOT_AVAILABLE" && isAllowed == true && isBanned == false) {
        $.ajax({
            url: "php/setLive.php",
            data: {workerId: worker, task: task, dbName: gup('dbName')},
            dataType: "text",
            success: function(d) {
                //url = "wait.php";
                //alert(url);

                // at this point, "Accept HIT" button on MTurk was clicked on
                // IF this workerId already finished the tutorial, send directly to wait.php
                // otherwise, send to third party url

                $.ajax({
                    url: "php/checkTutorialLog.php", 
                data: {workerId: worker, task: task, dbName: gup('dbName')}, 
                dataType: "text", 
                async: false, 
                success: function (dd) {
                    if (dd == 1) {
                        url = "wait.php?";
                    } else {
                        // alert(window.location); 
                        url = "tutorial.php"; 
                        url += "?tutPageUrl=" + gup('tutPageUrl');
                    }
                    url += "&waitPageUrl=" + gup('waitPageUrl'); 
                    url += "&workerId=" + gup('workerId');
                    url += "&assignmentId=" + gup('assignmentId');
                    url += "&hitId=" + gup('hitId');
                    url += "&turkSubmitTo=" + gup('turkSubmitTo');
                    url += "&task=" + gup('task');
                    url += "&min=" + gup('min');
                    url += "&instructions=" +  gup('instructions');
                    url += "&dbName=" +  gup('dbName');
                    url += "&requireUniqueWorkers=" +  gup('requireUniqueWorkers');
                    //alert("inside url check: " + url); 
                    var requireUniqueWorkers = gup('requireUniqueWorkers');
                    if(requireUniqueWorkers == "true"){
                        $.ajax({
                            type: 'POST',
                            url: 'php/uniqueWorkers.php',
                            data: {workerId: gup("workerId"), task: gup('task'), assignQualification: true, turkSubmitTo: gup('turkSubmitTo'), dbName:gup('dbName')},   
                            success: function (d) {
                                console.log(d);
                                window.location = url; 
                            },
                            failure:function(f){
                                console.log(f)
                            }
                        });
                    }
                    else{
                        window.location=url;
                    }
                },
                fail: function () {
                    alert("something in checkTutorialLog.php failed!"); 
                },
                }); 
            },
                fail: function() {
                    alert("setLive failed!")
                },
        });
    }
});

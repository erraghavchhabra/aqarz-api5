
<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<!------ Include the above in your HEAD tag ---------->

<button id="p">click</button>
<form id="uploadForm" enctype="multipart/form-data" >
    <input type="file" id="file" name="file">
</form>

<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script>
    $( "#p" ).on( "click", function() {
        var headers10 = {
            //      'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': '*/*',
            'Content-Type': 'application/json',
            'Cache-Control': 'no-cache',
            'Accept-Encoding': 'gzip, deflate, br',
            'v': 'v3',
        };

        var url = 'https://apibeta.aqarz.sa/api/add/estate/site';

        var formData = new FormData();
        var imagefile = document.querySelector('#file');
        formData.append("image", imagefile.files[0]);
        formData.append("id", '10');
        //formData.append("image", imagefile.files[0]);
        axios.post(url,formData, {headers: headers10})
            .then(function (response) {


                if (response) {

                    console.log(response);

                }


                /*     group.groups1 = null;
                     group.groups2 = null;
                     group.groups1 = response.data.items.group1;
                     group.groups2 = response.data.items.group2;*/


                //   this.$forceUpdate();
            })
            .catch(function (error) {
                console.log(error);
            });
    });

</script>


<!------ Include the above in your HEAD tag ---------->

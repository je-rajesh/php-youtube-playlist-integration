<html>

<head>
    <title>Upload playlist</title>
</head>

<body>
    <h1></h1>
    <!-- our form -->
    <form id='userForm'>
        <div><input type='text' name='playlist' placeholder='Playlist' /></div>
        <div><input type='submit' value='Submit' /></div>
    </form>
    <style type="text/css">
        table {
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }
    </style>
    <!-- where the response will be displayed -->
    <div id='response'></div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js "></script>
    <script>
        // $(document).ready(function(){
        $('#userForm').submit(function() {

            // show that something is loading
            $('#response').html("<b>Loading response...</b>");

            // Call ajax for pass data to other place
            $.ajax({
                    type: 'post',
                    url: 'process.php',
                    data: $(this).serialize() // getting filed value in serialize form
                })
                .done(function(data) { // if getting done then call.

                    // show the response
                    $('#response').html(data);

                })
                .fail(function() { // if fail then getting message

                    // just in case posting your form failed
                    alert("Posting failed.");

                });

            // to prevent refreshing the whole page page
            return false;

        });
        // });
    </script>

</body>

</html>

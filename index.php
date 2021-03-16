<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" integrity="sha512-HK5fgLBL+xu6dm/Ii3z4xhlSUyZgTT9tuc/hSrtw6uzJOvgRr2a9jyxxT1ely+B+xFAmJKVSTbpM/CuL7qxO8w==" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.12/dist/vue.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js" integrity="sha512-bZS47S7sPOxkjU/4Bt0zrhEtWx0y0CRkhEp8IckzK+ltifIIE9EMIMTuT/mEzoIMewUINruDBIR/jJnbguonqQ==" crossorigin="anonymous"></script>

    <title>playlists</title>
</head>

<body>
    <!-- form -->
    <div class="container mt-5" id="root">
        <form action="/request.php" method="post" ref="form" @submit.prevent="fetch">
            <div class="form-group">
                <label for="form-label">Input Youtube Id</label>
                <input type="text" class="form-control" v-model="yt_id">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>


        <div v-if="!show_table" class="mt-5">
            <h3>{{ alert_message }}</h3>
        </div>

        <div v-if="show_table">
            <div class="row mt-5">
                <h3 class="ml-3">Playlist</h3>
            </div>
            <div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>title</th>
                            <th>img</th>
                            <th>video count</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="(item, id) in playlists" :key="id">
                            <td>{{ item.playlistId }}</td>
                            <td>{{ item.title }}</td>
                            <td>
                                <img :src="item.imgurl" alt="image" class="img-thumnail" width="100" height="100">
                            </td>
                            <td>{{ item.videoCount }}</td>
                            <td>
                                <button class="btn btn-secondary" @click="refresh_item(item)"> <i class="fas fa-sync"></i></button>
                                <button class="btn btn-danger" @click="delete_item(item)"> <i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <!-- /form -->



    <script>
        new Vue({
            el: '#root',

            data() {
                return {
                    yt_id: 'apple',
                    playlists: [],
                    show_table: false,
                    alert_message: '',
                };
            },
            methods: {
                fetch() {
                    var self = this;
                    console.log(self.request_type);

                    var formdata = new FormData();
                    formdata.append('yt_id', self.yt_id);
                    formdata.append('request_type', 'get_playlists');

                    axios.post('/request.php', formdata)
                        .then(function(response) {
                            console.log(response.data);

                            if (response.data.status == 200) {
                                self.playlists = response.data.data;
                                self.show_table = true;
                            } else {
                                self.alert_message = response.data.message;
                                self.show_table = false;
                            }
                        })
                        .catch(function(response) {
                            alert('error');
                            console.log(response);
                        });
                },

                refresh_item(item) {
                    var formdata = new FormData();
                    var self =  this;

                    formdata.append('request_type', 'refresh_playlist');
                    formdata.append('playlist_id', item.playlistId);

                    axios.post("/request.php", formdata)
                        .then(function(response) {
                            console.log(response.data);
                            self.fetch();
                            // self.playlists = response.data.data;
                            // self.fetch();
                        })
                        .catch(function(response) {
                            console.log(response);
                        });
                },
                delete_item(item) {
                    var self = this;
                    var formdata = new FormData();

                    formdata.append('request_type', 'delete_playlist');
                    formdata.append('playlist_id', item.playlistId);

                    axios.post('/request.php', formdata)
                        .then(function(response) {
                            self.playlists = response.data.data;
                            console.log(response.data.data);
                        })
                        .catch(function(response) {
                            console.log(response);
                        })
                }
            }
        });
    </script>
</body>

</html>

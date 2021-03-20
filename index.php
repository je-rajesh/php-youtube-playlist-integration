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
        <form action="/request.php" method="post" ref="form" @submit.prevent="login">
            <div class="form-group">
                <label for="form-label">Input Youtube Id</label>
                <input type="text" class="form-control" v-model="yt_id">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>


        <div v-if="!show_table" class="mt-5">
            <h3>{{ alert_message }}</h3>
        </div>

        <div class="row mt-5 justify-content-between mr-0" v-if="authenticated">
            <h3 class="ml-3">Playlist</h3>
            <form class="form form-inline" @submit.prevent="create">
                <div class="form-group row mr-0 mb-3">
                    <label for="add_playlist" class="form-label mr-3">Create Playlist</label>
                    <input type="text" v-model="add_playlist" id="add_playlist" class="form-control">
                    <button class="btn btn-success ml-3" :disabled="creating_playlist">Add
                        <i class="fas fa-spinner fa-spin" v-if="creating_playlist"></i>
                    </button>
                </div>
            </form>
        </div>
        <div v-if="show_table">
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
                                <button class="btn btn-secondary" @click="refresh_item(item, id)"> <i class="fas fa-sync" :class="{ 'fa-spin': refreshing_id == id }"></i></button>
                                <button class="btn btn-danger" @click="confirm(item, id)"> <i class="fas fa-trash"></i></button>
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
                    yt_id: 'AIzaSyDUF3v8nCibiEEEL3677lSfjPMKWMNdPuQ',
                    authenticated: false,
                    playlists: [],
                    show_table: false,
                    alert_message: '',
                    add_playlist: '',
                    refreshing_id: NaN,
                    deleting_id: NaN,
                    creating_playlist: false,
                };
            },
            methods: {
                login() {
                    var formdata = new FormData();
                    var self = this;
                    formdata.append('yt_id', self.yt_id);
                    formdata.append('request_type', 'login');

                    axios.post('./request.php', formdata)
                        .then(function(response) {
                            // console.log(response.data);
                            // self.authenticated = true;
                            if (response.data.status == 200) {
                                alert('user logged in');
                                self.authenticated = true;
                                self.fetch();
                            } else if (response.data.status == 401) {
                                alert('unauthenticated');
                                self.show_table = false;
                                console.log(response.data);
                            } else {
                                alert(response.data.message);
                            }
                        })
                        .catch(function(response) {
                            console.log(response);
                            alert('error occured!');
                        });
                },
                fetch() {
                    var self = this;
                    // console.log(self.request_type);

                    var formdata = new FormData();
                    formdata.append('yt_id', self.yt_id);
                    formdata.append('request_type', 'get_playlists');

                    axios.post('./request.php', formdata)
                        .then(function(response) {
                            console.log(response.data);

                            if (response.data.status == 200) {
                                self.playlists = response.data.data == null ? [] : response.data.data;

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

                refresh_item(item, id) {
                    var formdata = new FormData();
                    var self = this;
                    self.refreshing_id = id;

                    formdata.append('request_type', 'refresh_playlist');
                    formdata.append('playlist_id', item.playlistId);

                    formdata.append('yt_id', self.yt_id);
                    // self.$set(self.playlists[id], 'refreshing', true);

                    axios.post("./request.php", formdata)
                        .then(function(response) {
                            console.log(response.data);
                            let b = response.data.data;

                            self.refreshing_id = NaN;
                            self.$set(self.playlists, id, response.data.data);

                        })
                        .catch(function(response) {
                            // self.$set(self.playlists[id], 'refreshing', false);
                            self.refreshing_id = NaN;
                            alert('error occured.');
                            console.log(response);
                        });
                },
                delete_item(item, id) {
                    var self = this;
                    var formdata = new FormData();

                    formdata.append('yt_id', self.yt_id);
                    formdata.append('request_type', 'delete_playlist');
                    formdata.append('playlist_id', item.playlistId);

                    axios.post('./request.php', formdata)
                        .then(function(response) {
                            // self.playlists = response.data.data;
                            self.playlists.splice(id, 1);
                            console.log(response.data.data);
                        })
                        .catch(function(response) {
                            console.log(response);
                        })
                },
                confirm(item) {
                    if (confirm('Do you really want to delete it?')) this.delete_item(item);
                },

                create() {
                    var self = this;

                    var formdata = new FormData();

                    formdata.append('yt_id', self.yt_id);
                    formdata.append('request_type', 'create_playlist');
                    formdata.append('playlist_id', self.add_playlist);

                    self.creating_playlist = true;
                    axios.post('./request.php', formdata)
                        .then(function(response) {

                            // first check for success 201(created) response
                            if (response.data.status == 201) {
                                if (self.playlists.length != 0)
                                    self.playlists.splice(0, 0, response.data.data);
                                else {
                                    self.playlists.push(response.data.data);
                                }
                                alert('playlist added');
                            }

                            // else check for 204 status 
                            else if (response.data.status == 204) {
                                alert('playlist already exists');
                            }
                            // else check for other error 
                            else {
                                console.log(response.data);
                                alert(response.data.message);
                            }

                            self.creating_playlist = false;
                        })
                        .catch(function(response) {
                            console.log(response);
                            // alert(response.data.message);
                            self.creating_playlist = false;
                        });
                    self.add_playlist = '';
                }
            }
        });
    </script>
</body>

</html>

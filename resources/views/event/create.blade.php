@extends('layouts.app')

@section('header')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/skins/ui/oxide/content.min.css">
@endsection

@section('title', 'Events')
@section('content')

<div class="row" id="createEvent">
    <div class="col-12 mb-12">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-white">
                    Create Event
                </h6> 
            </div>
            <div class="card-body">
                <form id="createEventForm" action="{!! action('EventController@store') !!}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="name">Name</label>
                        <input 
                            id="name"
                            class="form-control"
                            type="text"
                            name="name"
                            v-model="name"
                            v-bind:class="{'is-invalid': (validationError && (name == null || name.trim() == ''))}">

                        <span v-show="validationError && (name == null || name.trim() == '')" style="display: none" class="text-danger">Enter a name for the event</span>
                    </div>

                    <div class="mb-3 row">
                        <div class="col">
                            <label class="form-label" for="date">Date</label>
                            <input
                                id="date"
                                class="datepicker form-control"
                                type="text"
                                name="date"
                                v-model="date"
                                :disabled="expireInf"
                                :placeholder="expireInf && 'Never expires'" 
                                v-bind:class="{'is-invalid': (validationError && (date == null || date.trim() == ''))}"
                                ref="date">
                            <span v-show="validationError && (date == null || date.trim() == '')" style="display: none;" class="text-danger">Fill out a valid event date</span>
                        </div>

                        <div class="col">
                            <label class="form-label" for="startTime">Start time</label>
                            <input 
                                id="startTime"
                                class="form-control"
                                type="text"
                                name="startTime"
                                v-model="startTime"
                                v-bind:class="{'is-invalid': (validationError && (startTime == null || startTime.trim() == ''))}"
                                ref="startTime">

                            <span v-show="validationError && (startTime == null || startTime.trim() == '')" style="display: none" class="text-danger">Enter a start time for the event</span>
                        </div>

                        <div class="col">
                            <label class="form-label" for="endTime">End time</label>
                            <input 
                                id="endTime"
                                class="form-control"
                                type="text"
                                name="endTime"
                                v-model="endTime"
                                v-bind:class="{'is-invalid': (validationError && (endTime == null || endTime.trim() == ''))}"
                                ref="endTime">

                            <span v-show="validationError && (endTime == null || endTime.trim() == '')" style="display: none" class="text-danger">Enter a end time for the event</span>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <div class="col-12 col-lg-6">
                            <label class="form-label">Description</label>
                            <textarea id="description" name="description">

                            </textarea>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label">ATC Notes</label>
                            <textarea id="notes" name="notes">
                                
                            </textarea>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="coverImage" class="form-label">Cover image</label>
                        <input 
                            class="form-control" 
                            type="file" 
                            id="coverImage"
                            name="coverImage"
                            v-bind:class="{'is-invalid': (validationError && (coverImage == null || coverImage == ''))}"
                            ref="coverImage"
                            accept="image/png, image/jpeg, image/jpg, image/svg">
                        <span v-show="validationError && (coverImage == null || coverImage == '')" style="display: none" class="text-danger">You need to upload an cover image</span>
                    </div>

                    @if(Auth::user()->isAdmin())
                        <div class="mb-3">
                            <input class="form-check-input" type="checkbox" value="" id="sendNotification" name="sendNotification">
                            <label class="form-check-label ps-1" for="sendNotification">
                                Send discord notification
                            </label>
                        </div>
                    @endif

                    <button type="submit" id="submit_btn" class="btn btn-success mt-4" v-on:click="submit">Create event</button>
                </form>
            </div>
        </div>
    </div>

</div>

@endsection

@section('js')
<!-- Flatpickr --> 
@vite(['resources/js/flatpickr.js', 'resources/sass/flatpickr.scss'])
@vite('resources/js/vue.js')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        const createEvent = createApp({
            data() {
                return {
                    name: null,
                    date: null,
                    startTime: null,
                    endTime: null,
                    validationError: false,
                    description: null,
                    notes: null,
                    coverImage: null,
                }
            },
            methods:{
                validate(){
                    var validated = true;

                    if(
                        !this.name ||
                        this.name.trim() == '' ||
                        !this.date ||
                        this.date.trim() == '' ||
                        !this.startTime ||
                        this.startTime.trim() == '' ||
                        !this.endTime ||
                        this.endTime.trim() == '' ||
                        !this.coverImage ||
                        this.coverImage == ''
                    )
                        validated = false;

                    return validated;
                },
                submit(event) {
                    event.preventDefault();

                    this.desc = tinymce.get("description").getContent();
                    this.notes = tinymce.get("notes").getContent();

                    this.coverImage = this.$refs.coverImage.value;

                    if(this.validate()){
                        document.getElementById('createEventForm').submit();
                    } else {
                        this.validationError = true;
                    }
                }
            },
            mounted(){
                tinymce.init({
                    selector: 'textarea#description',
                    placeholder: 'Event description',
                    paste_data_images: false,
                    browser_spellcheck: true,
                    height: 300,
                    plugins: [
                        'charmap', 'preview', 'insertdatetime', 'help', 'wordcount', 'code'
                    ],
                    toolbar: 'undo redo | blocks | ' +
                    'bold italic backcolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat | help | code',
                    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
                });

                tinymce.init({
                    selector: 'textarea#notes',
                    placeholder: 'ATC notes',
                    paste_data_images: false,
                    browser_spellcheck: true,
                    height: 300,
                    plugins: [
                        'charmap', 'preview', 'insertdatetime', 'help', 'wordcount', 'code'
                    ],
                    toolbar: 'undo redo | blocks | ' +
                    'bold italic backcolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat | help | code',
                    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
                });

                this.$refs.date.flatpickr({ disableMobile: true, minDate: "{!! date('Y-m-d') !!}", maxDate: "{!! date('Y-m-d', strtotime('3 months')) !!}", dateFormat: "Y-m-d", locale: {firstDayOfWeek: 1 } });
                this.$refs.startTime.flatpickr({ disableMobile: true, enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true});
                this.$refs.endTime.flatpickr({ disableMobile: true, enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true});
            },

        }).mount('#createEvent');
    });

</script>
@endsection
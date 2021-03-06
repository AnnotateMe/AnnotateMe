@extends('layouts.app')

@section('content')

    @error('id_data1')
    <div class="container">
        <div class="d-flex justify-content-center">
            <div class="alert alert-warning w-50 text-center" role="alert"><b>Error</b></div>
        </div>
    </div>
    @enderror
    @error('id_data2')
    <div class="container">
        <div class="d-flex justify-content-center">
            <div class="alert alert-warning w-50 text-center" role="alert"><b>Error</b></div>
        </div>
    </div>
    @enderror

    @error('category')
    <div class="container">
        <div class="d-flex justify-content-center">
            <div class="alert alert-warning w-50 text-center" role="alert"><b>You must select a category</b></div>
        </div>
    </div>
    @enderror

    <form method="post" action="{{ route('project.annotate.post', ['id' => $data->id_prj]) }}">
        @csrf
        <div class="container">
            <div class="row">
                <div class="col">
                    <h2>{{ str_replace('_', ' ', $data->name_prj) }}</h2>
                </div>

                <div class="col-md-auto">

                    @if(session('annotation')['id_mode'] == 2)
                        <p>Remains : {{ session('annotation')['nb_annotation_remaining'] }} </p>
                    @else
                        <p id="time">Remains : loading...</p>
                    @endif

                </div>
            </div>

            <div class="row mt-4 mb-5">
                <div class="col-sm-12 col-md-6" style="width: 500px;height: 350px">
                    <img class="img-display rounded" style="margin: 0 auto;display: block;"
                         src="{{ asset('storage/app/datas/' . $pictures[$number[0]]['pathname_data']) }}">
                    <input type="hidden" name="id_data1" value="{{ $pictures[$number[0]]['id_data'] }}">
                </div>

                <div class="col-sm-12 col-md-6 mt-sm-5 mt-md-0" style="width: 500px;height: 350px">
                    <img class="img-display rounded" style="margin: 0 auto;display: block;"
                         src="{{ asset('storage/app/datas/' . $pictures[$number[1]]['pathname_data']) }}">
                    <input type="hidden" name="id_data2" value="{{ $pictures[$number[1]]['id_data'] }}">
                </div>


                <div class="inputs col-sm mt-5">

                    <h5>Do these images look like each other ?</h5>
                    @foreach ($categorys as $category)
                        <div class="stacked custom-control custom-checkbox rounded ">
                            <input type="radio" class="d-none pl-2"
                                   id="customCheck{{$category->id_cat}}" name="category" value="{{$category->id_cat}}">
                            <label for="customCheck{{$category->id_cat}}" class="btn btn-outline-primary">
                                {{$category['label_cat']}}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>


            <div class="container mt-5">
            <h3>My confidence level :</h3>
                <div class="row">
                    <output class="bg-primary text-white mt-3" for="fader" id="output">Confidence:</output>
                    <input type="range" id="fader" class="col custom-range testRange mt-3"
                    name="expert_sample_confidence_level" min="0" max="300" step="1"
                    oninput="outputUpdate(value)" onchange="center(value)">
                </div>

                <button type="submit" class="mt-3 btn-block btn btn-lg btn-primary" id="next">Next</button>
            </div>
        </div>
    </form>

    <style type="text/css">

        #output {
            margin-right: -15px;
            width: 180px;
            border-radius: 20px;
            padding: 10px;
            text-align:center;
            font-size: 1.2em;
        }

        .inputs {
            display: flex;
            flex-direction: column;
        }
        .stacked {
            flex: 1;
            display: flex;
            justify-content: center;
            flex-direction: column;
            padding: 0px;
        }

        .stacked label {
            height: 100%;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.2em;
        }
    </style>

    <script type="text/javascript">
        function outputUpdate(vol) {
            var output = document.querySelector('#output');
            
            if (vol > 100 && vol < 200)
            {           
                output.value = "Confident"
            }
            else if (vol <= 100)
            {           
                output.value = "Not Confident"
            }
            else
            {           
                output.value = "Realy Confident"
            }
        }
        
        function center(vol) {
            var output = document.querySelector('#output');
            var fader = document.querySelector('#fader');
            
            if (vol > 100 && vol < 200)
            {           
                output.value = "Confident"
                fader.value = 150;
            }
            else if (vol <= 100)
            {           
                output.value = "Not Confident"
                fader.value = 0;
            }
            else
            {           
                output.value = "Realy Confident"
                fader.value = 300;
            }
        }

        document.addEventListener("DOMContentLoaded", function(){
            let bNext = document.getElementById("next")
            bNext.disabled = true

            let bInputs = document.querySelectorAll(".stacked label")
            for (let bInput of bInputs) {

                bInput.addEventListener("click", function(){
                    for (let b of bInputs) {
                        b.classList.add('btn-outline-primary')
                        b.classList.remove('btn-primary')
                    }

                    this.classList.remove('btn-outline-primary')
                    this.classList.add('btn-primary')

                    bNext.disabled = false;
                })
            }
        })
            {{-- If the limit of project is in time --}}
        @if(session()->has('annotation.time_end_annotation'))
            let countDown = () => {
                let date_limit = new Date("{{ session()->get('annotation.time_end_annotation') }}")

                let now = 0
                let calcDiff = 1

                $.ajax({
                    url: "{{ asset('date.php') }}",
                    complete: function (response) {
                        now = new Date(response.responseText)

                        let diff = (date_limit - now) / 1000
                        let minutes = Math.floor(diff / 60)
                        diff -= minutes * 60
                        let secondes = Math.floor(diff)
                        document.querySelector('#time').innerText = "Remains : " + minutes + ":" + secondes
                        calcDiff = (date_limit - now) / 1000
                        if(calcDiff <= 0) {
                            document.querySelector('#time').innerText = "Elapsed time, last annotation"
                            clearInterval(idCountDown)
                        }


                    },
                    error: function () {
                        document.querySelector('#time').innerText = "Remains : Error"
                    }
                })
            }

            countDown()

            let idCountDown = setInterval(countDown, 1000);
        @endif
    </script>
@endsection

@extends('layouts.app')

@section('title', 'NBA Games')

@section('content')
    <div class="mb-4">
        <h2 class="text-xl font-bold">Select a Date to View NBA Games</h2>
        <div id="calendar"></div>
    </div>
    <input type="date" id="gameDate" class="border p-2 mt-4 hidden">
    <h2 id="title" class="text-xl font-bold hidden"></h2>
    <div id="games" class="mt-2 grid gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 grid-cols-1"></div>
@endsection

@section('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.js'></script>
    <style>
        .fc-disabled {
            pointer-events: none;
            background-color: #e2e8f0;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let events = @json($events);

            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events,
                height: 600,
                dateClick: function(info) {
                    const dateString = info.dateStr;
                    const event = events.find(event => event.start.split('T')[0] === dateString);
                    if (event) {
                        document.getElementById('gameDate').value = dateString;
                        fetchGames(dateString);
                    }
                },
                eventClick: function(info) {
                    const dateString = info.event.startStr.split('T')[0];
                    document.getElementById('gameDate').value = dateString;
                    fetchGames(dateString);
                },
                datesSet: function(info) {
                    addEventToCalendar(info);
                    disableCellsWithoutGames();
                }
            });

            calendar.render();

            function addEventToCalendar(info) {
                axios.get(`/getGames?date=${info.start.getFullYear()}-${info.start.getMonth()+1}`)
                    .then(response => {
                        for (e of response.data) {
                            const event = events.find(event => event.start && event.start === e.start);
                            if (!event) {
                                calendar.addEvent(e);
                                events.push(e);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Failed to initialize season:', error);
                    });
            }

            function disableCellsWithoutGames() {
                const calendarCells = calendarEl.getElementsByClassName('fc-daygrid-day');
                for (let cell of calendarCells) {
                    const dateString = cell.getAttribute('data-date');
                    const event = events.find(event => event.start && event.start.split('T')[0] === dateString);
                    if (!event) {
                        cell.classList.add('fc-disabled');
                    } else {
                        cell.classList.remove('fc-disabled');
                    }
                }
            }

            disableCellsWithoutGames();
        });

        function fetchGames(date) {
            axios.get(`/games?date=${date}`)
                .then(response => {
                    const games = response.data;
                    const gamesContainer = document.getElementById('games');
                    const title = document.getElementById('title');
                    title.classList.remove('hidden');
                    title.innerHTML = `${date} Results`;
                    gamesContainer.innerHTML = '';
                    games.forEach(game => {
                        const gameElement = document.createElement('div');
                        gameElement.classList.add('p-4', 'border');
                        gameElement.innerHTML = `
                            <div>Home <span class="font-bold">${game.home_team}</span> VS <span class="font-bold">${game.away_team}</span> Away</div>
                            <div>Score: ${game.home_score} - ${game.away_score}</div>
                            <div>Winner: <span class="font-bold">${game.winning_team}</span></div>
                        `;
                        gamesContainer.appendChild(gameElement);
                    });
                });
        }

        // window.onload = function() {
        //     axios.get('/initialize')
        //         .then(response => {
        //             console.log(response.data.message);
        //         })
        //         .catch(error => {
        //             console.error('Failed to initialize season:', error);
        //         });
        // };
    </script>
@endsection

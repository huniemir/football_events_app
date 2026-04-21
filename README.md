# Football Events Application

Simple application for handling football events - recruitment task.

---

In this task, I focused primarily on improving the quality of event processing and data integrity.

## Implemented changes:
- I expanded the event model with additional details (event time, player data) and handling the 'goal' event.
- I expanded input data validation to ensure integrity and prevent invalid events from being recorded.
- I secured the updating of statistics before race conditions, as I noticed that problems could arise when there was a time discrepancy between reading statistics and the writing function.
- I implemented a simple system that allows users to receive notifications by polling statistics with the last update date entered. This decision was dictated by time limits.
- I expanded tests to verify my changes.

## Further development plans for the application:
- Introducing real-time communication via SSE or Websocket to improve communication optimization.
- Moving the saving and reading of statistics to Redis to optimize reading.
- Introducing the ability to rebuild (recover) statistics based on event history. - Further upgrade the project structure, e.g., by embedding it in an MVC framework (Laravel/Symfony)
- Introducing the ability to save and read events from MySQL for improved data persistence

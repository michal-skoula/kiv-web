-- Migration name: 2026-11-04T22:20:42 create_posts_table.sql

-- START UP MIGRATION BLOCK --

CREATE TABLE posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    body TEXT,
    status TEXT NOT NULL,
    is_pinned INTEGER NOT NULL DEFAULT 0,
    view_count INTEGER NOT NULL DEFAULT 0,
    tags TEXT NOT NULL DEFAULT '[]',
    created_at TEXT NOT NULL,
    published_at TEXT
);

INSERT INTO posts (title, body, status, is_pinned, view_count, tags, created_at, published_at) VALUES
    ('Why tram 22 is always late', 'A look at one month of delay data.', 'published', 1, 1840, '["tram","delays"]', '2026-09-01T08:00:00+02:00', '2026-09-02T09:00:00+02:00'),
    ('Mapping every metro entrance', 'Station entrances, plotted by line.', 'published', 0, 512, '["metro","maps"]', '2026-09-03T10:15:00+02:00', '2026-09-04T12:00:00+02:00'),
    ('The quietest bus stop in Prague', 'Ranking stops by daily departures.', 'published', 0, 233, '["bus","stops"]', '2026-09-05T14:30:00+02:00', '2026-09-06T08:30:00+02:00'),
    ('Night trams, ranked', 'Which night line covers the most ground.', 'published', 0, 967, '["tram","night"]', '2026-09-08T22:10:00+02:00', '2026-09-09T07:45:00+02:00'),
    ('What GTFS actually contains', 'A tour of the feed files.', 'published', 1, 1402, '["gtfs","data"]', '2026-09-10T09:00:00+02:00', '2026-09-11T09:00:00+02:00'),
    ('Rush hour in numbers', 'Departures per minute across the network.', 'published', 0, 388, '["data"]', '2026-09-12T16:20:00+02:00', '2026-09-13T10:00:00+02:00'),
    ('Ferries are public transport too', 'The PID boats nobody remembers.', 'published', 0, 145, '["ferry"]', '2026-09-15T11:05:00+02:00', '2026-09-16T11:00:00+02:00'),
    ('Longest line in the network', NULL, 'draft', 0, 0, '["bus"]', '2026-09-18T13:40:00+02:00', NULL),
    ('Delay heatmap, first attempt', 'Colours still need work.', 'draft', 0, 0, '["delays","maps"]', '2026-09-20T17:55:00+02:00', NULL),
    ('Weekend vs weekday timetables', 'How much service drops on Sunday.', 'published', 0, 276, '["data","timetables"]', '2026-09-22T08:25:00+02:00', '2026-09-23T08:00:00+02:00'),
    ('Old notes on the Golemio API', 'Superseded by the newer write-up.', 'archived', 0, 91, '["api"]', '2026-09-24T19:00:00+02:00', '2026-09-25T09:30:00+02:00'),
    ('Golemio API rate limits', 'What the limits mean for polling.', 'published', 0, 624, '["api"]', '2026-09-26T10:50:00+02:00', '2026-09-27T10:00:00+02:00'),
    ('Which stops share the most lines', NULL, 'draft', 0, 0, '[]', '2026-09-28T15:15:00+02:00', NULL),
    ('Funicular to Petrin, by the data', 'Small line, strange timetable.', 'published', 0, 410, '["funicular"]', '2026-09-30T12:00:00+02:00', '2026-10-01T09:00:00+02:00'),
    ('Replacement buses during closures', 'Tracking one summer of track work.', 'archived', 0, 58, '["bus","closures"]', '2026-10-02T09:45:00+02:00', '2026-10-03T09:00:00+02:00'),
    ('Fastest route across the city', 'Metro wins, mostly.', 'published', 0, 733, '["metro","routes"]', '2026-10-05T18:30:00+02:00', '2026-10-06T08:00:00+02:00'),
    ('Accessible stations overview', 'Step-free access by line.', 'published', 0, 352, '["metro","accessibility"]', '2026-10-08T11:20:00+02:00', '2026-10-09T10:30:00+02:00'),
    ('Regional trains in the PID', NULL, 'draft', 0, 0, '["train"]', '2026-10-12T14:00:00+02:00', NULL),
    ('First and last departures', 'When the network wakes up and sleeps.', 'published', 0, 199, '["timetables"]', '2026-10-15T07:10:00+02:00', '2026-10-16T07:30:00+02:00'),
    ('Semester project progress notes', 'Writing the ORM by hand.', 'draft', 1, 0, '["meta"]', '2026-11-04T21:00:00+01:00', NULL);

-- END UP MIGRATION BLOCK --

-- START DOWN MIGRATION BLOCK --

DROP TABLE posts;

-- END DOWN MIGRATION BLOCK --

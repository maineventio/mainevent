# MainEvent

The open-source event tracking and analytics platform.
[Check out the project website at mainevent.io](http://mainevent.io)

MainEvent provides functionality similar to the core features of MixPanel, for tracking events and users in your
web or mobile app, with these benefits:
* Free!
* Internal to your network: no data costs, absolute privacy
* Fast deploy on AWS (see [Getting Started]())
* Built from standard components - easy to access and remix the data
* Open source - customize to your project and needs

"I know, I just want to install it!" - skip to [Getting Started]() below or the full [Installation]() wiki.

## Why

Every company I know is either paying too much for event tracking, or has built their own system.
Too much effort is wasted reinventing the wheel on what should be a standard utility for companies and projects,
so MainEvent solves that.  Generate events on any scale, attach any attributes to them, and report on them
in a clean easy dashboard.

## Features

* blah

## Who

I'm Jeff Magnusson, co-founder and Co-CEO of SuperRewards, which was recently acquired by Perk.com.  This is my
second company and second exit :) At SuperRewards I got pissed off at the cost of basic event tracking services
when you scale up, and heard from the companies that I work with and founders that I coach about their work
building event trackers in-house.  This is a huge waste of time for any startup: you should be putting your
engineering talent into solving X, not solving event tracking.  Unless you're MixPanel, that's their job,
but they're expensive at scale.  When things make me mad I need to see a solution emerge, so I've launched
MainEvent as an open-source project.
I still lead [SuperRewards](http://superrewards.com) under the [Perk.com](http://perk.com) umbrella, and I run a
mailing list [Startup Codex](http://startupcodex.com) that curates the best writing on management and leadership,
check it out.
Find me: [@jmagnuss on Twitter](http://twitter.com/jmagnuss), [LinkedIn](http://linkedin.ca/jeffmagnusson),
or email me directly, my GitHub username is my GMail address.

## Design & Architecture

### Approach
There are a million ways to implement event tracking.  MainEvent takes a minimum-code approach and seeks to
use as many off the shelf products as possible, especially AWS managed services, for two reasons:
* AWS managed services mean minimum maintenance.  Don't task your DevOps guy (hey @rlawrie!) with maintaining Memcache or Elastic Search, use AWS and forget about it.
* AWS products have ways of fitting together easily.  If you want to also have your MainEvent data pumped into a RedShift database for querying or another product, that can be turned on with well documented options.

The code for the reporting dashboard is in Laravel PHP, which is sublime to work with, and continues the minimal-code approach.
"Why PHP?  ____ is much faster for this!" Because PHP is universally understood, and Laravel is genius for web apps like the dashboard.
Glue pieces in MainEvent, like ETL scripts, should be done in whatever is best supported by the tools and performs the best.
There is some Node.js, some shell scripts, etc...

MainEvent is always a work in progress, and we are experimenting to find the best sequence of products and systems for our goals.
We document the design and the choices made on the [Architecture & Design](http://TODO) wiki page.

### Basic Flow

##










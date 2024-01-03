# UT maintenance calendar IRC bridge

This project is a simple UT (University of Twente) maintenance calendar IRC bridge, which leverages the maintenance calendar RSS feed, a local sqlite status database and an external irker service to post updates from the maintenance calendar to a configured IRC channel.

Directly based on the Cachet IRC bridge project: https://github.com/bobvandevijver/cachet-irc-bridge.

All settings are configured using the environment it is executed under. You can either create a `.env.local` file containing your configuration, or set your environment values before running the command.

## Installation

1. Clone the repository
2. Run `composer install -o --apcu-autoloader --no-dev` to install the dependencies
3. Make sure the user that runs the script can write in the `var` directory

## Configuration

The following environment vars must be set for the bridge to work:

| Env var         | Example                                                                                                                | Description                                                                      |
|-----------------|------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------|
| `FRONTEND_HOST` | `https://status.yourdomain.local/`                                                                                     | The base domain of the maintenance calendar instance, including a trailing slash |
| `IRKER_SERVER`  | `irker.yourdomain.local`                                                                                               | The dns name of the irker server                                                 |
| `IRC_ENDPOINT`  | `irc://irc.tweakers.net:6667/bobv,isnick` <br>for a nick or <br>`irc://irc.tweakers.net:6667/#tweak` <br>for a channel | The irker formatted IRC endpoint                                                 |

## Automatic run

You can run the command periodically with any tool you like.

For example, every minute using standard cron:

```
*/1 * * * * ircbot /usr/bin/php -f /opt/utwente-maintenance-calendar-irc-bridge/command.php -- -q
```

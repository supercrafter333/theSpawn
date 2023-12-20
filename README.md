[![Discord](https://img.shields.io/badge/chat-on%20discord-7289da.svg)](https://discord.gg/ca6cWPpERp)
# <a href="https://supercrafter333.github.io/theSpawn/"><img src="https://github.com/supercrafter333/theSpawn/blob/master/icon.png?raw=true" width="26" float="left" style="border-radius: 0.3rem"></a> theSpawn - v2.0.5  [![](https://poggit.pmmp.io/shield.state/theSpawn)](https://poggit.pmmp.io/p/theSpawn) [![](https://poggit.pmmp.io/shield.dl.total/theSpawn)](https://poggit.pmmp.io/p/theSpawn)
✨ **The best teleportation plugin and the best MSpawns alternative!** ✨

<br />

**This plugin is a much better MSpawns alternative! Help me to make this plugin better!**

This plugin won't be out of date, it will get better developed from time to time. If you need a much better MSpawns alternative, theSpawn is it :)

### Features
- **All MSpawns features**:
  - set/change/remove spawns [default: active]
  - set/change/remove hub [default: active]
  - Teleport to spawn/hub [default: active]
  - Aliases [default: active]
  - use/don't use hub server
  - API
  - Custom messages
  - Config
- **Position validation/check** [default: inactive]
- **Homes** [default: active]
  - home editing
  - Max home count permission [default: inactive]
- **Warps** [default: active]
  - warp editing
  - warp permissions
  - warp icons
- **TPAs** [default: active]
- **Random-Hubs** [default: inactive]
- **`/back`-command** - Teleports you to the position where you have died [default: active]
- **PlayerWarps** [default: active]
  - player-warp editing
  - player-warp icons
  - max player-warp count permission [default: inactive]
- **Forms / UIs** [default: active]
- Toast-Notifications for special features [default: inactive]
- WaterdogPE hub-transfer support
- Teleport to hub on join [default: active]
- Teleport to hub on death [default: active]
- Multiple languages [default: English (BE)]
- Always Up-To-Date (available for PM5 and PHP 8.1)
- Highly customizable (you can disable and enable every feature)

### TODOs
- [X] Homes
- [X] Command files
- [X] Warps
- [X] Random-Hubs ([#12](https://github.com/supercrafter333/theSpawn/issues/12))
- [X] TPAs ([#14](https://github.com/supercrafter333/theSpawn/issues/14))
- [X] Warp icons ([#31](https://github.com/supercrafter333/theSpawn/issues/31))
- [X] Warp editing ([#35](https://github.com/supercrafter333/theSpawn/issues/35))
- [X] Home editing ([#38](https://github.com/supercrafter333/theSpawn/issues/38))
- [X] `/back`-command ([#43](https://github.com/supercrafter333/theSpawn/issues/43))
- [X] Hub teleport on death ([#39](https://github.com/supercrafter333/theSpawn/issues/39))
- [X] Option to see the number of people in the world when using the warp-form ([#57](https://github.com/supercrafter333/theSpawn/issues/57))
- [X] Add player warps ([#59](https://github.com/supercrafter333/theSpawn/issues/59))
- [X] Add PM5 support (currently hybrid support for PM4 & PM5)
- [X] Add PHP 8.1 support
- [X] Add toast-notifications for special features


### Supported versions
| theSpawn version(s) | PocketMine-MP version(s) | PHP version(s) |
|---------------------|--------------------------|--------------|
| 1.8.x (EOL)         | 4.4.0+                   | 8.0.x        |
| 2.0.x               | 5.0.0+       | 8.1.x        |


### Report Bug
You've found a Bug?
- Go to [Issues](https://github.com/supercrafter333/theSpawn/issues)
- Click on [New Issue](https://github.com/supercrafter333/theSpawn/issues/new/choose)
- Write your bug with all Informations that you have down
- Send Issue
- Now wait, I'll answer you soon

### Commands
| **Command**                        | **Description**                                              |
|------------------------------------|--------------------------------------------------------------|
| `/setspawn`                        | Set the spawn of a world                                     |
| `/delspawn`                        | Remove spawn of a world                                      |
| `/spawn`                           | Teleport you to spawn                                        |
| `/sethub [randomHubs: number/int]` | Set the hub of the server                                    |
| `/delhub [randomHubs: number/int]` | Remove the hub of the server                                 |
| `/hub`                             | Teleport you to hub                                          |
| `/setalias <alias> <world>`        | Set an alias                                                 |
| `/delalias <alias>`                | Remove an alias                                              |
| `/aliases`                         | Prints a list of all aliases or open a menu to edit aliases. |
| `/sethome <home>`                  | Set a home                                                   |
| `/delhome <home>`                  | Remove a home                                                |
| `/home <home>`                     | Teleport you to a home                                       |
| `/setwarp <warp>`                  | Set a warp                                                   |
| `/delwarp <warp>`                  | Remove a warp                                                |
| `/warp <warp>`                     | Teleport you to a warp                                       |
| `/tpa <player>`                    | Send a teleportation answer to a player                      |
| `/tpahere <player>`                | Send a teleportation answer to a player                      |
| `/tpaccept <player>`               | Accept a tpa                                                 |
| `/tpdecline <player>`              | Decline a tpa                                                |
| `/editwarp [warp]`                 | Edit a warp                                                  |
| `/edithome [home]`                 | Edit a home                                                  |
| `/back`                            | Teleports you to the spot where you died                     |
| `/playerwarp <subcommand>`         | Manage, create, remove, or teleport you to a player-warp.    |

### Permissions
| **Permission**              | **Description**                                                         | **Default** |
|-----------------------------|-------------------------------------------------------------------------|-------------|
| `theSpawn.bypass`           | Bypass permission (includes all permissions of theSpawn)                | op          |
| `theSpawn.spawn.cmd`        | Command permission for `/spawn`                                         | everyone    |
| `theSpawn.setspawn.cmd`     | Command permission for `/setspawn`                                      | op          |
| `theSpawn.delspawn.cmd`     | Command permission for `/delspawn`                                      | op          |
| `theSpawn.hub.cmd`          | Command permission for `/hub`                                           | everyone    |
| `theSpawn.sethub.cmd`       | Command permission for `/sethub`                                        | op          |
| `theSpawn.delhub.cmd`       | Command permission for `/delhub`                                        | op          |
| `theSpawn.alias.cmd`        | Command permission for every alias-command                              | everyone    |
| `theSpawn.aliases.cmd`      | Command permission for `/aliases`                                       |             |op
| `theSpawn.setalias.cmd`     | Command permission for `/setalias`                                      | op          |
| `theSpawn.removealias.cmd`  | Command permission for `/removealias`                                   | op          |
| `theSpawn.setwarp.cmd`      | Command permission for `/setwarp`                                       | everyone    |
| `theSpawn.delwarp.cmd`      | Command permission for `/delwarp`                                       | everyone    |
| `theSpawn.warp.cmd`         | Command permission for `/warp`                                          | everyone    |
| `theSpawn.warp.NAME`        | Allows you to teleport to a permission-saved-warp                       | individual  |
| `theSpawn.warp.admin`       | Bypass permission to be allowed to teleport to every warp               | op          |
| `theSpawn.homes`            | Bypass permission for home-related things                               | op          |
| `theSpawn.homes.unlimited`  | Bypass permission to create unlimited homes                             | op          |
| `theSpawn.sethome.cmd`      | Command permission for `/sethome`                                       | everyone    |
| `theSpawn.delhome.cmd`      | Command permission for `/delhome`                                       | everyone    |
| `theSpawn.home.cmd`         | Command permission for `/home`                                          | everyone    |
| `theSpawn.edithome.cmd`     | Command permission for `/edithome`                                      | everyone    |
| `theSpawn.tpa.cmd`          | Command permission for `/tpa`                                           | everyone    |
| `theSpawn.tpahere.cmd`      | Command permission for `/tpahere`                                       | everyone    |
| `theSpawn.tpaccept.cmd`     | Command permission for `/tpaccept`                                      | everyone    |
| `theSpawn.tpdecline.cmd`    | Command permission for `/tpdecline`                                     | everyone    |
| `theSpawn.back.cmd`         | Command permission for `/back`                                          | everyone    |
| `theSpawn.playerwarp.cmd`   | Command permission for `/playerwarp`                                    | everyone    |
| `theSpawn.pwarps.unlimited` | Bypass permission to create unlimited player-warps                      | op          |
| `theSpawn.pwarps.1`         | Allows you to create 1 player-warp                                      | everyone    |
| `theSpawn.pwarps.AMOUNT`    | Allows you to create AMOUNT (please insert a number there) player-warps | individual  |


### License
This plugin is licensed under the [Apache License 2.0](/LICENSE)! Plugin by supercrafter333!

### Credits
**Information:** This plugin is inspired by [MSpawns](https://github.com/EvolSoft/MSpawns).

Owner: supercrafter333

Translator: AyzrixYTB, MrBlasyMSK

Icon: HannesTheDev [Thank you]

Discord-Tag: [`supercrafter333#4062`](https://discordapp.com/users/511252471616897024)


### Join the community
[![Discord Banner](https://discordapp.com/api/guilds/847099444465238036/widget.png?style=banner3)](https://discord.gg/ca6cWPpERp)

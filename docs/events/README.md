# Events
> Documentation is a WIP.


This module exposes the following events through the [Nails Events Service](https://github.com/nails/common/blob/master/docs/intro/events.md) in the `nails/module-admin` namespace.

> Remember you can see all events available to the application using `nails events`



- [Nails\Admin\Events::ADMIN_STARTUP](#admin-startup)
- [Nails\Admin\Events::ADMIN_READY](#admin-ready)



<a name="admin-startup"></a>
### `Nails\Admin\Events::ADMIN_STARTUP`

Fired when admin starts to load.

**Receives:**

> ```
> none
> ```


<a name="admin-ready"></a>
### `Nails\Admin\Events::ADMIN_READY`

Fired when admin is ready but before the controller is executed

**Receives:**

> ```
> none
> ```

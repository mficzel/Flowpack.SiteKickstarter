# Flowpack.SiteKickstarter

Kickstarter to create Documents and Contents ... very WIP ... do not use this. 

```
./flow  kickstart:sitepackage Vendor.Site
```

```
./flow  kickstart:document Vendor.Site SpecialPage --property author:string --property date:datetime
```

```
./flow  kickstart:content Vendor.Site Figure --property text:richtext --property image:image
```

All exceeding arguments are used as property definitions. 
 
The following property types are supported: string, bool, datetime, plaintext, richtext, image, images, asset, assets, reference, references 


# Flowpack.SiteKickstarter

Kickstarter to create Documents and Contents

```
./flow  kickstart:content Vendor.Site Text text:richtext
```

```
./flow  kickstart:document Vendor.Site SpecialPage author:string date:datetime
```

All argulments after the nodeName are used as property definitions. 
The following property types are supported: string, bool, datetime, plaintext, richtext, image, images, asset, assets, reference, references 


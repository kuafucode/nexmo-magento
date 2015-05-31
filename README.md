# nexmo-magento
Hi, I'm Ryan and I had created a nexmo based magento extension, enabling billing fraud control by sending a verify request to billing address number and 2 level authentication of admin user account by seding a verify request to admin phone number(if phone number set for the admin account), here is a brifeof how the extension works:

1, CONFIGURE EXTENSION
configuration menu: system->configuration->nexmo
When you turn on billing verification, it will add an additional field in onepage checkout revire page, and a nexmo vifify request will be send to your billing phone number automatically or upon request.
When you turn on admin verification, it will add an additional field in admin login page, and a nexmo virify reqest will be send to your admin account phone number upon request.

2, FRAUD CONTROL (ONE PAGE CHECKOUT)
Credit cart fraud can be a business killer, by sending a verify request to the billing phone number, we can make sure the customer is the actual card holder.
after you save payment on one page checkout, a verify request will be sent automatically

3, 2 level admin authentication
you can login admin account and change your phone number, via 'system->permission->user'.

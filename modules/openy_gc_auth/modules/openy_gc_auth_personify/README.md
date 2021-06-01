## Open Y Virtual YMCA Personify SSO Auth.

Provides Open Y Virtual YMCA Personify SSO authentication provider
 based on <i>Personify</i> module.

### Integration with Personify

Add Personify credentials to your settings.php file:

```
# Personify SSO data.
$config['personify.settings']['prod_wsdl'] = '';
$config['personify.settings']['stage_wsdl'] = '';
$config['personify.settings']['vendor_id'] = '';
$config['personify.settings']['vendor_username'] = '';
$config['personify.settings']['vendor_password'] = '';
$config['personify.settings']['vendor_block'] = '';

# Personify Prod endpoint.
$config['personify.settings']['prod_endpoint'] = '';
$config['personify.settings']['prod_username'] = '';
$config['personify.settings']['prod_password'] = '';

# Personify Stage endpoint.
$config['personify.settings']['stage_endpoint'] = '';
$config['personify.settings']['stage_username'] = '';
$config['personify.settings']['stage_password'] = '';
```

### Additional configuration

To be abe to login with Personify you have to add to your settings.php file
<i>login URL</i> for stage and prod environments:

```
# Personify login URL.
$config['openy_gc_auth_personify.settings']['prod_url_login'] = '';
$config['openy_gc_auth_personify.settings']['stage_url_login'] = '';

```

### How to configure your Personify for Virtual Y

#### 1. Create Personify SSO credentials

1.1 Log on to your SSO Setup portal (&lt;Your ebuzinessdomain&gt;/ SSO
/SSO_IMS_Admin/AdminLogin.aspx
1.2 Under SSO vendor Click -&gt; New
1.2.1 Create SSO Account
  - Enter Name of your choice (example OpenY Integration)
  - Leave Identifier with the default value listed
  - Enter Username of your choice (openy.integration)
  - Leave Password/Block Encryption at 32 Bits
  - Click New Password to generate Password
  - Click New Block to Generate Block
  - Enter 6 digit random number for License Key
  - Rest of the fields can remain blank/left unchecked
  - Click Save
1.2.2 Assign Access to Web Services

1.2.2.1 Open SSO Account created in earlier step

1.2.2.2 Scroll down and Grant access to: (If any of these calls are missing in your
environment, please log a support case with Personify/contact your
project manager)

  - a). **SSOCustomerTokenIsValid** : Verifies that the Customer&#39;s token is
valid. Tokens are single use, therefore a new token will be
returned if the provided token is valid.
  - b). **VendorTokenEncrypt**: Returns the encrypted Vendor token. This
method allows a Vendor to create a valid token without having to
implement Rijndael encryption.
  - c). **CustomerTokenDecrypt**: Returns the decrypted Customer token.
This method allows a Vendor to decrypt a Customer Token without
having to implement Rijndael decryption.
  - d). **SSOCustomerGetByCustomerToken**: Returns SSO Customer
Information. This method allows a Vendor to retrieve SSO
customer information by Customer Token.
  - e) **TIMSSCustomerIdentifierGet**: This allows the TIMSS Vendor to
set TIMSS customer ID for SSO Customer.

#### 2. Create Personify DataServices credentials

**The end point is &lt;Your ebuzinessdomain&gt;/PersonifyDataServices/PersonifyData.svc**

The login credentials are a standard Personify user login created from P360 -&gt; Security -&gt; User
and Group Setup -&gt; User Setup

**Note:**

1. **Suggested username: OpenY.Integration**
2. Assign the minimum permission needed to login.

#### 3. Stored Procedure

The custom stored procedure **[usr_OpenY_Member_Access]** is used to verify if the user logging in is a member that is allowed to access the Virtual membership.

The stored procedure returns “APPROVED” for members who have an order in the Membership subsystem that matches ALL the conditions below:
- Has a product with a product class of “DUES”
- Has a line status code of either A (Active), T (Term at end) or G (Grace)
- Has a fulfill status code of either A (Active), T (Term at end) or G (Grace)
- Has a Grace date >= today

The procedure will “Approve” the Ship-To Member on the order and members on the membership order (from the ORDER_MBR_ASSOCIATE) table. If the conditions above are not met, the stored procedure will return “DENIED”

**ACTION NEEDED**

- use this SQL-procedure code as starting point:https://gist.github.com/anpolimus/b3788f1ac4b54962a6c43f798104fde7
- Make any changes to the business logic that many be needed for your association
- Open a ticket to Personify support to create the stored procedure on your environment or work with your project/account manager as needed.
- Contact sujan.Vatturi@Ymcasv.org if needed.

#### Information that needs to be provided to Y-USA/agency
- SSO Credentials & endpoint
  - Wsdl : <Your ebusinessdomain>/SSO/webservice/service.asmx
  - Vendor Id: From step 1 (b) (2)
  - Vendor Username: From step 1 (b) (3)
  - Vendor Password: From step 1 (b) (5)
  - Vendor Block: From step 1 (b) (6)
- Data Service Credentials & endpoint (From Step2)
  - Endpoint:  
  - Username:
  - Password:
  - Input Parameters: 
  - Org_id: <Your org_id)
  - Org_Unit_id: <your org Unit ID>
- eBusiness login
  - URL to your ebusiness

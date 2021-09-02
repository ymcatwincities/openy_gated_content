# Welcome to Open Y Virtual Y smoke tests documentation

In order for Virtual Y app being tested in a short timeframe, please follow steps below

## Configuration

### User

Administrator

### Steps

1. Login as admin
2. Go to Virtual Y -> Virtual YMCA Settings -> Auth settings (/admin/openy/virtual-ymca/gc-auth-settings)
3. Verify there is ""Custom provider"" option in the list
4. Go to the Edit form 
5. Verify you can edit settings and they are saved correctly. 

### Expected Results

The form for custom authentication in Vitrual Y is working

## Check migration

### User

Administrator

### Steps

1. Login as admin
2. Go to Virtual Y -> Virtual YMCA Settings -> Auth settings (/admin/openy/virtual-ymca/gc-auth-settings)
3. Under Migration Settings find link to the form where you can upload CSV file 
4. Prepare CSV file with some test users based on the example https://github.com/fivejars/openy_gated_content/tree/master/modules/openy_gc_auth/modules/openy_gc_auth_custom#about
5. Upload CSV file 
6. Make sure upload was successful 
7. Under Migration Settings find a link to the form where you can run import
8. Verify the number of processed items is equal to number of records in the CSV
9. Go to People page (/admin/people)
10. Verify and confirm you can see users created during import. 
11. Verify users have role ""Virtual YMCA""
12. Verify all imported users should have status ""Blocked""

### Expected results

1. There is a form to upload CSV file before import 
2. There is a form to run/rollback import new users from CSV
3. After import new users created with the ""Blocked"" status. 

## Check form

### User

Anonymous

### Steps

1. Open Virtual Y landing page 
2. Verify you can see a block with a sign in form (onlye two fields email and captcha)
3. Verify you can enter email and access gated content

### Expected results

1. There is a login form on the landing page 
2. Login form works and give access to gated content

## Login with email verification

### User

Administrator / Anonymous

### Steps

1. Login as admin
2. Go to Virtual Y -> Virtual YMCA Settings -> Auth settings (/admin/openy/virtual-ymca/gc-auth-settings)
3. Choose ""Custom provider"" option in the list
4. Go to the Edit form 
5. Enable checkbox (if disabled) ""Enable Email verification""
6. Logout 
7. Go to Virtual Y landing page 
8. Enter email 
9. Verify you see a green message that verification link has been sent 
10. Open link from the received email 
11. Verify you got access to gated content 

### Expected results

1. Email verification settings provides the ability confirm email by sending a unique link that gives access to gated content 
2. User sess a message about sent email with instructions 
3. Link from the email opens gated content 
4. Email verification is needed only once

## Login without email verification 

### User

Administrator / Anonymous

### Steps

1. Login as admin
2. Go to Virtual Y -> Virtual YMCA Settings -> Auth settings (/admin/openy/virtual-ymca/gc-auth-settings)
3. Choose ""Custom provider"" option in the list
4. Go to the Edit form 
5. Disable checkbox (if enabled) ""Enable Email verification""
6. Logout 
7. Go to Virtual Y landing page 
8. Enter email 
9. Verify you got access to gated content 

### Expected results

After entering email user gets access to gated content

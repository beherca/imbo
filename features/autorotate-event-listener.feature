Feature: Imbo provides an event listener for auto rotating images based on EXIF-data
    In order to ensure a correct image rotation of new images
    As an Imbo admin
    I must enable the AutoRotate event listener

    Background:
        Given "tests/Imbo/Fixtures/640x160_rotated.jpg" exists in Imbo with identifier "9bd43d8936bea4c27cc3c1bcafaba7da"

    Scenario: Fetch the auto rotated image
        Given I use "publickey" and "privatekey" for public and private keys
        And I include an access token in the query
        When I request the added image as a "png"
        Then I should get a response with "200 OK"
        And the "Content-Type" response header is "image/png"
        And the width of the image is "640"
        And the height of the image is "160"

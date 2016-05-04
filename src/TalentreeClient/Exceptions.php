<?php

class Talentree_Error extends Exception {}
class Talentree_HttpError extends Talentree_Error {}

/**
 * The parameters passed to the API call are invalid or not provided when required
 */
class Talentree_ValidationError extends Talentree_Error {}

/**
 * The provided API key is not a valid Talentree API key
 */
class Talentree_Invalid_Key extends Talentree_Error {}
